<?php

namespace App\Listeners;

use App\Events\UserEvent;
use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle(UserEvent $event)
    {
        $message = " - A new user was added {$event->user->email} via {$event->source}";

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
