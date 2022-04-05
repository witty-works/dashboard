<?php

namespace App\Listeners;

use App\Http\Middleware\PostHogMiddleware;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;
use PostHog\PostHog;

class PosthogBilling
{
    public function handle(WebhookReceived $event)
    {
        if (config('posthog.enabled') && !empty($event->payload['data']['object']['customer'])) {
            $team = Cashier::findBillable($event->payload['data']['object']['customer']);

            if ($team) {
                PostHog::capture([
                    'distinctId' => $team->owner->posthogId(),
                    'event' => 'stripe.' . $event->payload['type'],
                    '$groups' => [PostHogMiddleware::POSTHOG_ORGANIZATION_TYPE => $team->posthogId()],
                ]);
            }
        }
    }
}
