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
            SlackAlert::message("A new team member was added to {$event->team->name}, total count is now at {$event->team->total_user_licenses_count}.");
        }
    }
}
