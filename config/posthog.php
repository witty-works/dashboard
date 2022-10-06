<?php

return [

    'js_enabled' => env('POSTHOG_JS_ENABLED', false),
    'enabled' => env('POSTHOG_ENABLED', false),
    'api_key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST'),
    'debug' => env('POSTHOG_DEBUG', false),
    'project_id' => env('POSTHOG_PROJECT_ID'),
    'personal_api_key' => env('POSTHOG_PERSONAL_API_KEY'),
];
