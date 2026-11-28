<?php

namespace App\Support;

/**
 * Whether the dashboard collects usage events (PostHog) and whether it shows
 * its analytics pages. A deployment may collect before the pages have
 * anything worth showing, so the pages have a switch of their own
 * (ANALYTICS_PAGES_ENABLED).
 */
class Analytics
{
    public static function collected(): bool
    {
        return (bool) config('posthog.enabled');
    }

    public static function pagesShown(): bool
    {
        return self::collected() && (bool) config('posthog.analytics_pages');
    }
}
