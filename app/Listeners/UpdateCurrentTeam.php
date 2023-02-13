<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamMemberRemoved;

class UpdateCurrentTeam
{
    public function handle(TeamMemberRemoved $event)
    {
        $event->user->switchTeam($event->user->ownedTeams->first());
    }
}
