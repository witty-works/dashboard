<?php

namespace App\Listeners;

use Spatie\SlackAlerts\Facades\SlackAlert;

class UserCompanyUpdatedSlackAlert
{
    public function handle($event)
    {
        $message = " - User {$event->user->email} set company name to {$event->user->company_name}";

        SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
    }
}
