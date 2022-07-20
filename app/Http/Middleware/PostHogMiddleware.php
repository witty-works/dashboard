<?php

namespace App\Http\Middleware;

use Closure;
use PostHog\PostHog;

class PostHogMiddleware
{
    public const POSTHOG_ID_PREFIX = 'dashboard:';
    public const POSTHOG_ORGANIZATION_TYPE = 'organization';

    public static $reset = false;

    public function handle($request, Closure $next)
    {
        if (config('posthog.enabled')) {
            PostHog::init(
                config('posthog.api_key'),
                ['host' => config('posthog.host')],
            );
        }

        return $next($request);
    }
}
