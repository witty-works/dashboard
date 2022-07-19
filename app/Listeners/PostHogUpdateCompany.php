<?php

namespace App\Listeners;

use App\Http\Middleware\PostHogMiddleware;
use PostHog\PostHog;

class PostHogUpdateCompany
{
    public function handle($event)
    {
        if (empty($event->team) || !config('posthog.enabled')) {
            return;
        }

        $team = $event->team;

        PostHog::groupIdentify([
            'groupType' => PostHogMiddleware::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $team->posthogId(),
            'properties' => [
                'name' => $team->name,
                'owner' => $team->owner->posthogId(),
                'impersonate_url' => config('app.url') . '/impersonate/take/' . $team->owner->id,
                'users' => $team->getTotalUserCount(),
                'stripe_plan' => $team->planId(),
            ]
        ]);
    }
}
