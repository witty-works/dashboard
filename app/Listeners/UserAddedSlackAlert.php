<?php

namespace App\Listeners;

use App\Events\UserEvent;
use Spatie\SlackAlerts\Facades\SlackAlert;

class UserAddedSlackAlert
{
    use SourceTrait;

    public function handle(UserEvent $event)
    {
        $message = sprintf(
            " - A new user was added %s via %s",
            $event->user->email,
            $this->getSource($event->user),
        );

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
