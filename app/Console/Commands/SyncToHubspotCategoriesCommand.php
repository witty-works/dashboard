<?php

namespace App\Console\Commands;

use App\Helpers\Hubspot;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SyncToHubspotCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:hubdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync category data from Hubspot HubDB';

    protected static $tableData = [];

    protected $defaultData = [
        'diversity_dimension_drivers' => [
            "casing" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "compounding" => [
                "category" => "orthography",
                "emoji" => "⚠️",
            ],
            "confused_words" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "grammar" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "misc" => [
                "category" => "orthography",
                "emoji" => "🤔",
            ],
            "orthography" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "punctuation" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "repetitions" => [
                "category" => "orthography",
                "emoji" => "⚠️",
            ],
            "typography" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "typos" => [
                "category" => "orthography",
                "emoji" => "❌",
            ],
            "corporate_rules" => [
                "category" => "corporate_rules",
                "emoji" => "❗",
                "translations" => [
                    "en" => [
                        "hs_name" => "Dictionary",
                    ],
                    "de" => [
                        "hs_name" => "Wörterbuch",
                    ],
                ],
            ],
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = storage_path('app/hubdb');
        $this->info("Publishing and reading HubSpot HubDB data into $path");

        $hubspot = new Hubspot();

        $tables = [
            'subcategories' => 'diversity_dimension_drivers_translations',
            'base_subcategories' => 'diversity_dimension_drivers',
            'proficiency_level_translations' => 'proficiency_levels_translations',
            'proficiency_levels' => 'proficiency_levels',
            'categories' => 'categories_translations',
            'base_categories' => 'categories',
        ];

        $data = [];
        foreach ($tables as $table => $alias) {
            $this->info("Fetch '$alias' data from '$table' table.");

            $hubspot->publishTable($table);
            $file = $hubspot->exportTable($table);

            //read csv headers
            $keys = $file->fgetcsv();
            array_shift($keys);

            // parse csv rows into array
            $rows = [];
            while ($row = $file->fgetcsv()) {
                if (empty($row[0])) {
                    continue;
                }

                $key = array_shift($row);
                $rows[$key] = array_combine($keys, $row);
                if (array_key_exists('is_active', $rows[$key]) && empty($rows[$key]['is_active'])) {
                    unset($rows[$key]);
                } else {
                    $rows[$key] = preg_replace('/font-size:[^;]+;/', '', $rows[$key]);
                }
            }

            $data[$alias] = $rows;
        }

        $langs = ['en', 'de'];
        foreach ($data as $alias => $tableData) {
            $this->info("Cleaning '$alias' data.");

            foreach ($tableData as $i => $row) {
                if (!str_ends_with($alias, '_translations')) {
                    foreach ($langs as $lang) {
                        $id = $row["translation_$lang"];
                        unset($row["translation_$lang"]);
                        if (empty($data[$alias . '_translations'][$id])) {
                            $this->warn("Missing translation '$lang' in '{$alias}': '{$row['name']}'");
                            continue;
                        }

                        $translation = $data[$alias . '_translations'][$id];
                        $translation = $this->cleanRow($translation);
                        unset($translation['sort']);

                        $row["translations"][$lang] = $translation;
                    }
                }

                if (!empty($row['inclusive'])) {
                    $row['inclusive'] = json_decode($row['inclusive']);
                }

                if (!empty($row['has_en_rules'])) {
                    $row['has_en_rules'] = $row['has_en_rules'] === 'true';
                }
                if (!empty($row['has_de_rules'])) {
                    $row['has_de_rules'] = $row['has_de_rules'] === 'true';
                }

                if (isset($row['canonical_url']) && !str_ends_with($row['canonical_url'], '/' . $row['hs_path'])) {
                    $this->warn("Canonical URL '{$row['canonical_url']}' does not end with '/{$row['hs_path']}' for '{$row['name']}'");
                }

                $converted_name = str_replace(['- und ', ' + ', ' / ', ' '], ['-und-', '-', '-', '-'], mb_strtolower($row['hs_name']));
                if (!empty($row['hs_path']) && !empty($row['hs_name']) && $row['hs_path'] != $converted_name) {
                    $this->warn("Page Title '{$row['hs_name']}' ('$converted_name') mis-aligned with Page Path '{$row['hs_path']}' for '{$row['name']}'");
                }

                $row = $this->cleanRow($row);
                if ($alias === 'categories_translations') {
                    unset($row['category']);
                }
                unset($row['subcategory_name']);
                if ($alias === 'diversity_dimension_drivers_translations') {
                    unset($row['diversity_dimension_driver']);
                }

                foreach (['lead_image', 'example_image', 'example_image_advanced', 'icon'] as $imageKey) {
                    if (array_key_exists($imageKey, $row)) {
                        $image = [];
                        if (!empty($row[$imageKey])) {
                            $image = explode(',', $row[$imageKey]);
                            $image = ['src' => $image[0], 'width' => $image[1] ?? '', 'height' => $image[2] ?? ''];
                            $image['alt'] = $row[$imageKey . '_alt_text'] ?? '';
                        }

                        unset($row[$imageKey . '_alt_text']);

                        $row[$imageKey] = $image;
                    }
                }


                $data[$alias][$i] = $row;
            }
        }

        foreach (array_keys($data) as $alias) {
            if (str_ends_with($alias, '_translations')) {
                continue;
            }

            $tableData = $data[$alias];

            $this->info("Restructuring '$alias' data.");

            $finalData = [];
            foreach ($tableData as $i => $row) {
                if ($alias === 'diversity_dimension_drivers') {
                    $proficiencyLevels = $row['proficiency_level'] === ''
                        ? [] : explode(',', $row['proficiency_level']);

                    $count = count($proficiencyLevels);
                    if ($count !== 1) {
                        $this->warn("Incorrect 'proficiency_level' count {$count} for '{$row['name']}'");
                    }

                    $row['proficiency_level'] = null;
                    foreach ($proficiencyLevels as $proficiencyLevel) {
                        if (isset($data['proficiency_levels'][$proficiencyLevel]['name'])) {
                            $row['proficiency_level'] = $data['proficiency_levels'][$proficiencyLevel]['name'];
                        }
                    }

                    $relatedSubcategories = $row['related_subcategories'] === ''
                        ? [] : explode(',', $row['related_subcategories']);

                    $row['related_subcategories'] = [];
                    foreach ($relatedSubcategories as $relatedSubcategory) {
                        if (isset($data['diversity_dimension_drivers'][$relatedSubcategory]['name'])) {
                            $row['related_subcategories'][] = $data['diversity_dimension_drivers'][$relatedSubcategory]['name'];
                        }
                    }

                    if ($row['category'] && $row['proficiency_level']) {
                        if (empty($data['categories'][$row['category']]['diversity_dimension_drivers'][$row['proficiency_level']])) {
                            if (empty($data['categories'][$row['category']]['diversity_dimension_drivers'])) {
                                $data['categories'][$row['category']]['diversity_dimension_drivers'] = [];
                            }

                            $data['categories'][$row['category']]['diversity_dimension_drivers'][$row['proficiency_level']] = [];
                        }

                        $data['categories'][$row['category']]['diversity_dimension_drivers'][$row['proficiency_level']][] = $row['name'];
                    }

                    if (!empty($row['translations'])) {
                        foreach ($row['translations'] as $lang => $translation) {
                            $canonicalURL = $lang === 'en'
                                ? 'https://www.witty.works/en/categories/'
                                : 'https://www.witty.works/de/kategorien/';

                            $canonicalURL .= $data['categories'][$row['category']]['translations'][$lang]['hs_path'];
                            $canonicalURL .= '/' . $translation['hs_path'];
                            if ($translation['canonical_url'] != $canonicalURL) {
                                $this->warn("Canonical url mismatch: {$translation['canonical_url']} vs. {$canonicalURL}");
                            }
                        }
                    }

                    if (isset($data['categories'][$row['category']]['name'])) {
                        $row['category'] = $data['categories'][$row['category']]['name'];
                    } elseif (empty($row['category'])) {
                        $this->warn("Missing category in '{$row['name']}'");
                    } else {
                        $this->warn("Missing category data for '{$row['name']}' / '{$row['category']}'");
                    }
                }

                if ($alias === 'categories') {
                    if (!empty($row['diversity_dimension_drivers'])) {
                        foreach (array_keys($row['diversity_dimension_drivers']) as $proficiencyLevel) {
                            sort($row['diversity_dimension_drivers'][$proficiencyLevel]);
                        }
                    }

                    if (!empty($row['translations'])) {
                        foreach ($row['translations'] as $lang => $translation) {
                            $canonicalURL = $lang === 'en'
                                ? 'https://www.witty.works/en/categories/'
                                : 'https://www.witty.works/de/kategorien/';

                            $canonicalURL .= $translation['hs_path'];
                            if ($translation['canonical_url'] != $canonicalURL) {
                                $this->warn("Canonical url mismatch: {$translation['canonical_url']} vs. {$canonicalURL}");
                            }
                        }
                    }
                }

                if (isset($finalData[$row['name']])) {
                    $this->warn("Overlap in '$alias' for '{$row['name']}'");
                }

                $finalData[$row['name']] = $row;
                unset($finalData[$row['name']]['name']);
            }


            if (array_key_exists('sort', $row)) {
                $callback = function ($a, $b) {
                    if ($a['sort'] == $b['sort']) {
                        return 0;
                    }

                    return ($a['sort'] < $b['sort']) ? -1 : 1;
                };
                uasort($finalData, $callback);
            } else {
                ksort($finalData);
            }

            if (!empty($this->defaultData[$alias])) {
                $finalData += $this->defaultData[$alias];
            }

            $json = json_encode($finalData, JSON_PRETTY_PRINT);
            $file = $path . "/$alias.json";
            file_put_contents($file, $json);
        }

        $this->info("Wrote HubSpot HubDB data to '$path'.");
    }

    protected function cleanRow($row)
    {
        unset($row['hs_created_at']);
        unset($row['hs_updated_at']);
        unset($row['hs_child_table_id']);
        unset($row['language']);
        unset($row['resources']);
        unset($row['category_name']);
        unset($row['excluded']);

        unset($row['introduction']);
        unset($row['solution']);

        return $row;
    }

    public static function loadTableData($tableName, $skipOrthography = false)
    {
        $tableNameFull = $tableName . ($skipOrthography ? '-no-orthograpbhy' : '-with-orthography');
        if (array_key_exists($tableNameFull, self::$tableData)) {
            return self::$tableData[$tableNameFull];
        }

        if (!array_key_exists($tableName, self::$tableData)) {
            $file = storage_path("app/hubdb/$tableName.json");
            self::$tableData[$tableName] = json_decode(file_get_contents($file), true);
        }

        $data = self::$tableData[$tableName];

        Collection::macro('toLocale', function (string $locale) {
            return $this->map(function ($value) use ($locale) {
                if (!empty($value['translations'][$locale])) {
                    $value['translation'] = $value['translations'][$locale];
                    unset($value['translations']);

                    if (isset($value['emoji'])) {
                        $value['translation']['emoji_name'] = $value['emoji'] . ' ' . $value['translation']['hs_name'];
                    }
                } else {
                    $value['translation'] = null;
                }

                return $value;
            });
        });

        $collection = collect($data);

        if ($skipOrthography) {
            $collection = $collection->reject(function ($metaData) {
                return $metaData['category'] === 'orthography';
            });
        }

        $locale = request()->header('X-App-Locale', app()->getLocale());
        self::$tableData[$tableNameFull] = $collection->toLocale($locale, $skipOrthography);

        return self::$tableData[$tableNameFull];
    }
}
