<?php

return [

    'js_enabled' => env('POSTHOG_JS_ENABLED', false),
    'enabled' => env('POSTHOG_ENABLED', false),
    'api_key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST'),
    'debug' => env('POSTHOG_DEBUG', false),
    'project_id' => env('POSTHOG_PROJECT_ID'),
    'personal_api_key' => env('POSTHOG_PERSONAL_API_KEY'),
    'insights_cache_time' => env('POSTHOG_INSIGHTS_CACHE_TIME', 3600),
    'dashboard_user_id_override' => env('POSTHOG_DASHBOARD_USER_ID'),
    'dashboard_team_id_override' => env('POSTHOG_DASHBOARD_TEAM_ID'),
    'delay_per_count' => env('POSTHOG_DELAY_PER_COUNT', 0.1),
    # https://posthog.com/docs/api#rate-limiting
    'rate' => [
        # 1200 per hour, use only 1000 per hour -> 16 per minute
        'limit' => env('POSTHOG_RATE_LIMIT', 16),
        'interval_seconds' => env('POSTHOG_RATE_INTERVAL_SECONDS', 60),
        'multiplier' => env('POSTHOG_RATE_MULTIPLIER', 3),
    ],
];
