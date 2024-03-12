<?php

namespace App\Listeners;

use App\Events\InvitedTeamMember;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberInvitedSlackAlert
{
    use SourceTrait;

    public function handle(InvitedTeamMember $event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
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
}
