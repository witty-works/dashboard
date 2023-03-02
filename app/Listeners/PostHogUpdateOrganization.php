<?php

namespace App\Listeners;

use App\Jobs\SyncToPosthog;
use Laravel\Jetstream\Events\TeamDeleted;

class PostHogUpdateOrganization
{
    public function handle($event)
    {
        if (empty($event->team)) {
            return;
        }

        dispatch(new SyncToPosthog($event->team, $event instanceof TeamDeleted));
    }
}
