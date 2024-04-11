<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamMemberAdded;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    public function handle(TeamMemberAdded $event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $event->user->refresh();

            $message = sprintf(
                " - A new team member '%s' was added to '%s' (%s), total count is now at %s via %s",
                $event->user->email,
                $event->team->name,
                $event->team->owner->email,
                $event->team->getTotalUserCount(),
                $event->user->source,
            );

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
