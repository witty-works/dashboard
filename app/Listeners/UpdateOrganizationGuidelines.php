<?php

namespace App\Listeners;

use App\Jobs\DeleteOrganizationFromNlpApi;
use App\Jobs\SyncOrganizationToNlpApi;
use Laravel\Jetstream\Events\TeamDeleted;

class UpdateOrganizationGuidelines
{
    public function handle($event)
    {
        $team = $event->team;

        if ($event instanceof TeamDeleted) {
            dispatch(new DeleteOrganizationFromNlpApi($team, 'medium'));
        } else {
            dispatch(new SyncOrganizationToNlpApi($team, 'high'));
        }
    }
}
