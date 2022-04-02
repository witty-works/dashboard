<?php

namespace App\Listeners;

use App\Events\AbstractSubscription;

class SyncStartRenwalDates
{
    public function handle(AbstractSubscription $event)
    {
        $subscription = $event->team->subscription();
        if ($subscription) {
            $subscription->syncStartRenewalAt();
        }
    }
}
