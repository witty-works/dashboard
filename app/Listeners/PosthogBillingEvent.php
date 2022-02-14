<?php

namespace App\Listeners;

use PostHog\PostHog;

class PosthogBillingEvent
{

    /**
     * Handle the event.
     *
     * @param Spark\Events\* $event
     * @return void
     */
    public function handle($event)
    {
        if (config('posthog.enabled')) {
            PostHog::capture([
                'distinctId' => $event->billable->postHogId(),
                'event' => get_class($event),
                '$groups' => ['company' => $event->billable->posthogTeamId()],
            ]);
        }
    }
}
