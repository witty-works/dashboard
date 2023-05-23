<?php

return [
    'enabled' => env('HUBSPOT_ENABLED', false),
    'js_enabled' => env('HUBSPOT_JS_ENABLED', false),
    'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
    'hub_id' => env('HUBSPOT_HUB_ID', '24904016'),
    'form_id' => env('HUBSPOT_FORM_ID', "01dc84ed-dd08-4f21-a445-abb71e37cf0d"),
    # https://developers.hubspot.com/docs/api/usage-details#rate-limits
    'rate' => [
        # ~2 API requests per Job (fetch + write) => 150 per 10s / 2 = 75 = 7.5 per second
        # optionally 1 search requests => 4 per second
        'limit' => env('HUBSPOT_RATE_LIMIT', 35),
        'interval_seconds' => env('HUBSPOT_RATE_INTERVAL_SECONDS', 10),
        'multiplier' => env('HUBSPOT_RATE_MULTIPLIER', 3),
    ],
];
