<?php

namespace App\Listeners;

use App\Jobs\SyncUserToPosthog;

class PostHogUpdateUser
{
    public function handle($event)
    {
        if (!empty($event->user)) {
            dispatch(new SyncUserToPosthog($event->user));
        }

        if (!empty($event->team)) {
            dispatch(new SyncUserToPosthog($event->team->owner));
        }
    }
}
