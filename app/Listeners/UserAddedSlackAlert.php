<?php

namespace App\Listeners;

use Illuminate\Support\Facades\App;
use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle($event)
    {
        if (!config('slack-alerts.webhook_urls.default')) {
            return;
        }

        SlackAlert::message(App::environment() . " - A new user was added {$event->user->email}.");
    }
}
