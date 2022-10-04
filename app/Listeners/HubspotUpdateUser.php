<?php

namespace App\Listeners;

use App\Jobs\SyncUserToHubSpot;
use App\Models\User;

class HubspotUpdateUser
{
    public function handle($event)
    {
        if (!empty($event->user)) {
            $user = $event->user;
        } elseif (!empty($event->team)) {
            $user = $event->team->owner;
        }

        if ($user instanceof User) {
            dispatch(new SyncUserToHubSpot($user));
        }
    }
}
