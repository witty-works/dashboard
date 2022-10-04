<?php

namespace App\Listeners;

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
        }
    }
}
