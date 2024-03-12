<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $message = " - A new team member '{$event->user->email}' was added to '{$event->team->name}' ('{$event->team->owner->email}'), total count is now at {$event->team->getTotalUserCount()} via {$event->source}";

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
