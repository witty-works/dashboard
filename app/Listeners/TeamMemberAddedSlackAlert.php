<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamMemberAdded;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    public function handle(TeamMemberAdded $event)
    {
        // This used to fire only for teams without a subscription. Billing is
        // gone, so it fires for every team.
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
