<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamMemberRemoved;

class UpdateCurrentTeam
{
    public function handle(TeamMemberRemoved $event)
    {
        $user = $event->user;
        $team = $user->ownedTeams()->first() ?? $user->teams()->first();

        if ($team) {
            $user->switchTeam($team);
        } else {
            // In no team any more (invite only): no current team either.
            $user->forceFill(['current_team_id' => null])->save();
        }
    }
}
