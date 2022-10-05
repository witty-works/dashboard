<?php

namespace App\Listeners;

use App\Events\AbstractSubscription;
use App\Jobs\SyncUserToHubSpot;

class HubspotUpdateUser
{
    public function handle($event)
    {
        if (!empty($event->user)) {
            dispatch(new SyncUserToHubSpot($event->user));
        }

        if (!empty($event->team)) {
            dispatch(new SyncUserToHubSpot($event->team->owner));

            if ($event instanceof AbstractSubscription) {
                foreach ($event->team->users as $user) {
                    dispatch(new SyncUserToHubSpot($user));
                }
            }
        }
    }
}
