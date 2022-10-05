<?php

namespace App\Listeners;

use App\Events\AbstractSubscription;
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

            if ($event instanceof AbstractSubscription) {
                foreach ($event->team->users as $user) {
                    dispatch(new SyncUserToPosthog($user));
                }
            }
        }
    }
}
