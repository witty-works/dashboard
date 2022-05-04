<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    public function handle($event)
    {
        if (!config('slack-alerts.webhook_urls.default')) {
            return;
        }

        $subscription = $event->team->subscription();
        if (!$subscription) {
            $team = $event->team;
            SlackAlert::message("A new team member was added to {$team->name}, total count is now at {$team->getTotalUserCount()}.");
        }
    }
}
