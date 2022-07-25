<?php

return [

    'js_enabled' => env('POSTHOG_JS_ENABLED', false),
    'enabled' => env('POSTHOG_ENABLED', false),
    'api_key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST'),
    'debug' => env('POSTHOG_DEBUG', false),

];
