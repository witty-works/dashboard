<?php

namespace App\Listeners;

use App\Events\InvitedTeamMember;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberInvitedSlackAlert
{
    public function handle(InvitedTeamMember $event)
    {
        // This used to fire only for teams without a subscription. Billing is
        // gone, so it fires for every team.
        $message = sprintf(
            " - A new team member '%s' was invited to '%s' (%s), total invited users at %d",
            $event->email,
            $event->team->name,
            $event->team->owner->email,
            $event->team->teamInvitations()->count(),
        );

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
