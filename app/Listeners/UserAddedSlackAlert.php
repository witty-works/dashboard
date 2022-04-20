<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle($event)
    {
        if (!config('slack-alerts.webhook_urls.default')) {
            return;
        }

        SlackAlert::message("A new user was added {$event->user->email}.");
    }
}
