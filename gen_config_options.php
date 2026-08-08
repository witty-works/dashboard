<?php

/**
 * Regenerates storage/app/data/config_options.json.
 *
 * The browser extension's options page has to offer the gender ending, gender
 * separator and generic-masculine choices, and a deployment running without the
 * dashboard has nowhere to learn their labels. Those labels live here, in the
 * dashboard's own lang files, so this writes them into the same data directory
 * that is copied to the NLP API alongside categories.json — one pipeline, one
 * source of wording.
 *
 * Values come from GuidelinesInterface rather than being listed out, so the two
 * surfaces cannot drift. The NLP API's enums are the authority on which values
 * exist; a value it accepts but the dashboard has no label for simply has none,
 * and clients fall back to showing the raw value (punctuation like `(-)` reads
 * fine untranslated).
 *
 * Run with the interface required directly rather than the Composer autoloader,
 * so it works without matching the app's PHP version:
 *
 *   php gen_config_options.php
 */

require __DIR__ . '/app/Models/GuidelinesInterface.php';

$fields = [
    'gendered_roles_format' => [
        'none'             => 'none',
        'both'             => 'both',
        'inclusive_gender' => 'inclusive_gender',
        'binary_gender'    => 'binary_gender',
    ],
    'german_gender_ending'    => \App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING,
    'french_gender_separator' => \App\Models\GuidelinesInterface::FRENCH_GENDER_SEPARATOR,
];

// The Inklusivum is not a separator spliced into Expert…in but its own
// paradigm (de Expertere), so it carries the VGD logo instead of an emoji.
// The API copies this file and emits the URL as Result.icon_image whenever
// this ending is the configured one; clients without it fall back to the
// emoji icon. Hosted on the website because that is the one origin whose
// media the repositories guarantee (see www.witty.works/AGENTS.md).
$icons = [
    'german_gender_ending' => [
        'de-e' => 'https://www.witty.works/assets/media/vgd-icon-bunt.svg',
    ],
];

$out = [];
foreach ($fields as $field => $map) {
    $entry = ['translations' => []];

    foreach (['en', 'de', 'fr'] as $locale) {
        $lang = require __DIR__ . "/resources/lang/$locale/guidelines.php";
        $labels = [];

        foreach ($map as $value => $key) {
            $short = str_replace('guidelines.', '', $key);
            if (!empty($lang[$short])) {
                $labels[(string) $value] = $lang[$short];
            }
        }

        $entry['translations'][$locale] = $labels;
    }

    if (!empty($icons[$field])) {
        $entry['icon_image'] = $icons[$field];
    }

    $out[$field] = $entry;
}

file_put_contents(
    __DIR__ . '/storage/app/data/config_options.json',
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);

echo "wrote storage/app/data/config_options.json\n";
