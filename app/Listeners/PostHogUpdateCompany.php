<?php

namespace App\Listeners;

use App\Http\Middleware\PostHogMiddleware;
use PostHog\PostHog;

class PostHogUpdateCompany
{
    public function handle($event)
    {
        if (empty($event->team)) {
            return;
        }

        $team = $event->team;

        $userCount = $stripe_plan = null;

        $subscription = $team->subscription();
        if ($subscription) {
            $userCount = $subscription->quantity;
            $stripe_plan = $subscription->planId();
        }

        PostHog::groupIdentify([
            'groupType' => PostHogMiddleware::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $team->posthogId(),
            'properties' => [
                'name' => $team->name,
                'users' => $userCount,
                'stripe_plan' => $stripe_plan,
            ]
        ]);
    }
}
