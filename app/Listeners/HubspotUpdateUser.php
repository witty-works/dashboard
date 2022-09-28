<?php

namespace App\Listeners;

use App\Jobs\SyncUserToHubSpot;

class HubspotUpdateUser
{
    public function handle($event)
    {
        if ($event->user) {
            $user = $event->user;
        } elseif ($event->team) {
            $user = $event->team->owner;
        }

        dispatch(new SyncUserToHubSpot($user));
    }
}
