<?php

namespace App\Listeners;

use Carbon\Carbon;

class UpdateUserLicenses
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if ($subscription && empty($subscription->update_user_licenses)) {
            $subscription->update_user_licenses_at = Carbon::now()->addHour();
            $subscription->save();
        }
    }
}
