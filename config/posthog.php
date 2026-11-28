<?php

return [

    'enabled' => env('POSTHOG_ENABLED', false),
    // The analytics pages, their API and menu entries, only while PostHog is
    // enabled too (App\Support\Analytics). Off, events are still collected.
    'analytics_pages' => (bool) env('ANALYTICS_PAGES_ENABLED', true),
    'api_key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST'),
    'debug' => env('POSTHOG_DEBUG', false),
    // How the client sends: lib_curl (PostHog's default) over the network.
    // file, for the tests only (phpunit.xml), writes each event as a line to
    // POSTHOG_FILENAME, which the client makes world-writable.
    'consumer' => env('POSTHOG_CONSUMER') ?: 'lib_curl',
    'filename' => env('POSTHOG_FILENAME'),
    'project_id' => env('POSTHOG_PROJECT_ID'),
    'personal_api_key' => env('POSTHOG_PERSONAL_API_KEY'),
    'insights_cache_time' => env('POSTHOG_INSIGHTS_CACHE_TIME', 3600),
    'dashboard_user_id_override' => env('POSTHOG_DASHBOARD_USER_ID'),
    'dashboard_team_id_override' => env('POSTHOG_DASHBOARD_TEAM_ID'),
    'delay_per_count' => env('POSTHOG_DELAY_PER_COUNT', 0.1),
    # The analytics pages count check_highlights events instead of
    # popover_open ones. Read here rather than with env() in the controller,
    # which returns null once the configuration is cached.
    'check_highlights_enabled' => (bool) env('CHECK_HIGHLIGHTS_ENABLED', false),
    # https://posthog.com/docs/api#rate-limiting
    'rate' => [
        # 1200 per hour, use only 1000 per hour -> 16 per minute
        'limit' => env('POSTHOG_RATE_LIMIT', 16),
        'interval_seconds' => env('POSTHOG_RATE_INTERVAL_SECONDS', 60),
        'multiplier' => env('POSTHOG_RATE_MULTIPLIER', 3),
    ],
];
