<?php

namespace App\Listeners;

use App\Jobs\SyncUserToNlpApi;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Events\TeamMemberRemoved;

/**
 * Tell the NLP API about a membership change: which teams a person is in
 * decides which of their keys still work. A person leaving their licence team
 * gets another of their teams as licence team, or none.
 */
class SyncMemberToNlpApi
{
    public function handle(TeamMemberAdded|TeamMemberRemoved $event): void
    {
        $user = $event->user->fresh();
        if ($user === null) {
            return;
        }

        if ($event instanceof TeamMemberAdded) {
            dispatch(new SyncUserToNlpApi($user, 'high'));

            return;
        }

        // DeleteTeam fires this before the team is gone, so it is left out
        // explicitly rather than by the database.
        $leaving = $event->team->id;
        if ((int) $user->license_team_id === $leaving) {
            $next = $user->allTeams()->first(fn ($team) => $team->id !== $leaving);
            $user->forceFill(['license_team_id' => $next?->id])->save();
        }

        dispatch(new SyncUserToNlpApi($user, 'high', $leaving));
    }
}
