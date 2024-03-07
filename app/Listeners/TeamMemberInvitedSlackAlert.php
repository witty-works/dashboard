<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberInvitedSlackAlert
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $message = " - A new team member '{$event->email}' was invited to '{$event->team->name}' ('{$event->team->owner->email}'), total invited users at {$$event->team->teamInvitations()->count()} via {$event->source}";

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
