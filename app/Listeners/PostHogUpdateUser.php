<?php

namespace App\Listeners;

use App\Http\Middleware\PostHogMiddleware;
use PostHog\PostHog;

class PostHogUpdateUser
{
    public function handle($event)
    {
        if (empty($event->user) || !config('posthog.enabled')) {
            return;
        }

        $user = $event->user;

        return PostHog::identify([
            'distinctId' => $user->posthogId(),
            'properties' => [
                '$groups' => [PostHogMiddleware::POSTHOG_ORGANIZATION_TYPE => $user->posthogTeamId()],
                'impersonate_url' => config('app.url') . '/impersonate/take/' . $user->id,
            ]
        ]);
    }
}
