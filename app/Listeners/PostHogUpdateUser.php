<?php

namespace App\Listeners;

use App\Jobs\SyncUserToPosthog;
use App\Models\User;

class PostHogUpdateUser
{
    public function handle($event)
    {
        if (!empty($event->user)) {
            $user = $event->user;
        } elseif (!empty($event->team)) {
            $user = $event->team->owner;
        }

        if ($user instanceof User) {
            dispatch(new SyncUserToPosthog($user));
        }
    }
}
