<?php

namespace App\Listeners;

use App\Events\AbstractSubscription;
use App\Events\UserEvent;
use App\Jobs\SyncUserToHubSpot;
use Laravel\Jetstream\Events\TeamEvent;

class HubspotUpdateUser
{
    public function handle($event)
    {
        if ($event instanceof UserEvent) {
            $user = $event->user;
        } elseif ($event instanceof TeamEvent || $event instanceof AbstractSubscription) {
            $user = $event->team->owner;
        } else {
            return;
        }

        dispatch(new SyncUserToHubSpot($user));
    }
}
