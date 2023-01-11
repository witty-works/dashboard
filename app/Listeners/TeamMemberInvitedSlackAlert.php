<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberInvitedSlackAlert
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $team = $event->team;
            $message = " - A new team member '{$event->email}' was invited to '{$team->name}' ('{$team->owner->email}'), total invited users at {$team->teamInvitations()->count()}.";

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
