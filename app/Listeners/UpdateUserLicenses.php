<?php

namespace App\Listeners;

use Carbon\Carbon;

class UpdateUserLicenses
{
    public function handle($event)
    {
        $subscription = $event->team->subscription();
        if ($subscription && empty($subscription->update_user_licenses)) {
            // for new subscriptions wait a week before charging
            if ($subscription->created_at > Carbon::now()->subMonth()) {
                $subscription->update_user_licenses_at = Carbon::now()->addWeek();
            } else {
                $subscription->update_user_licenses_at = Carbon::now()->addDay();
            }
            $subscription->save();
        }
    }
}
