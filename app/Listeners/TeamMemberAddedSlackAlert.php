<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $team = $event->team;
            $message = " - A new team member '{$event->user->email}' was added to '{$team->name}' ('{$team->owner->email}'), total count is now at {$team->getTotalUserCount()}.";

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
