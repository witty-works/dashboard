<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class CategoryDataHelper
{
    protected static $tableData = [];

    /** Where storage/app/data names the category icons and example images. */
    public const WEBSITE_MEDIA_URL = 'https://www.witty.works/assets/media/';

    public static function loadTableData($tableName, $skipOrthography = false)
    {
        $tableNameFull = $tableName . ($skipOrthography ? '-no-orthograpbhy' : '-with-orthography');
        if (array_key_exists($tableNameFull, self::$tableData)) {
            return self::$tableData[$tableNameFull];
        }

        if (!array_key_exists($tableName, self::$tableData)) {
            $file = storage_path("app/data/$tableName.json");
            self::$tableData[$tableName] = self::withMediaUrl(json_decode(file_get_contents($file), true));
        }

        $data = self::$tableData[$tableName];

        Collection::macro('toLocale', function (string $locale) {
            return $this->map(function ($value) use ($locale) {
                if (
                    empty($value['translations'][$locale])
                    && $locale === 'fr'
                    && !empty($value['translations']['en'])
                ) {
                    $locale = 'en';
                }

                if (!empty($value['translations'][$locale])) {
                    $value['translation'] = $value['translations'][$locale];

                    if (
                        empty($value['translation']['example_image']['src'])
                        && !empty($value['has_rules'])
                        && !in_array($locale, $value['has_rules'])
                    ) {
                        $otherLocale = reset($value['has_rules']);
                        if ($otherLocale && !empty($value['translations'][$otherLocale]['example_image']['src'])) {
                            $value['translation']['example_image'] = $value['translations'][$otherLocale]['example_image'];
                            $value['translation']['example_image_advanced'] = $value['translations'][$otherLocale]['example_image_advanced'];
                        }
                    }

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

    /** Points the image URLs in the data at APP_MEDIA_URL. */
    private static function withMediaUrl(array $data): array
    {
        $mediaUrl = rtrim((string) config('app.media_url'), '/') . '/';
        if ($mediaUrl === '/' || $mediaUrl === self::WEBSITE_MEDIA_URL) {
            return $data;
        }

        array_walk_recursive($data, function (&$value) use ($mediaUrl) {
            if (is_string($value) && str_starts_with($value, self::WEBSITE_MEDIA_URL)) {
                $value = $mediaUrl . substr($value, strlen(self::WEBSITE_MEDIA_URL));
            }
        });

        return $data;
    }
}
