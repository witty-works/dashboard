<?php

namespace App\Actions\Jetstream;

use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Events\TeamMemberRemoved;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     *
     * @param  mixed  $team
     * @return void
     */
    public function delete($team)
    {
        foreach ($team->allUsers() as $user) {
            TeamMemberRemoved::dispatch($team, $user);
        }

        $team->purge();
    }
}
