<?php

namespace App\Listeners;

use Illuminate\Support\Facades\App;
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
            SlackAlert::message(App::environment()." - A new team member '{$event->user->email}' was added to '{$team->name}' ('{$team->owner->email}'), total count is now at {$team->getTotalUserCount()}.");
        }
    }
}
