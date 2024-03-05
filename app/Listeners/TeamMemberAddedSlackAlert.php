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
            $source = request()->session()->get('login_source');
            $message = " - A new team member '{$event->user->email}' was added to '{$team->name}' ('{$team->owner->email}'), total count is now at {$team->getTotalUserCount()} via {$source}";

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
