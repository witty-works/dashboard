<?php

declare(strict_types=1);

use App\Localization\UserLanguageDetector;
use NielsNumbers\LaravelLocalizer\Detectors\BrowserDetector;

return [
    'supported_locales' => ['de', 'en', 'fr'],

    // The previous setup (laravel-localization with hideDefaultLocaleInURL
    // = false) always carried the locale in the URL; keep that behavior so
    // existing links and SEO-indexed URLs stay stable.
    'hide_default_locale' => false,

    'redirect_enabled' => true,

    'persist_locale' => [
        'session' => true,
        // Cookie persistence was not enabled under laravel-localization
        // (the localeCookieRedirect middleware was never applied).
        'cookie' => false,
    ],

    'detectors' => [
        UserLanguageDetector::class,
        BrowserDetector::class,
    ],

    // Per-locale override for writing direction. Keys must match the
    // locale codes used in `supported_locales`. Values: 'rtl' or 'ltr'.
    // Wins over the script-based detection in `LocaleDirection`.
    'locale_directions' => [],
];
