<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle($event)
    {
        $source = request()->session()->get('login_source');
        $message = " - A new user was added {$event->user->email} via {$source}";

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
