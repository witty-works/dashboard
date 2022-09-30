<?php

namespace App\Listeners;

use App\Jobs\SyncOrganizationToPosthog;
use Laravel\Jetstream\Events\TeamDeleted;

class PostHogUpdateOrganization
{
    public function handle($event)
    {
        if (empty($event->team)) {
            return;
        }

        if ($event instanceof TeamDeleted) {
            dispatch(new SyncOrganizationToPosthog($event->team, true));
        } else {
            dispatch(new SyncOrganizationToPosthog($event->team));
        }
    }
}
