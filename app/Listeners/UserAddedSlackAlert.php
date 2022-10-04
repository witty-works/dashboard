<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle($event)
    {
        $message = " - A new user was added {$event->user->email}";

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
