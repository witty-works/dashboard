<?php

namespace App\Listeners;

use App\Events\UserEvent;
use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    public function handle(UserEvent $event)
    {
        $message = sprintf(
            " - A new user was added %s via %s",
            $event->user->email,
            $event->user->source,
        );

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
