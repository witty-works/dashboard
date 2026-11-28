<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * Hand a team over to one of its admins. Only the owner can, for example
 * before deleting their account; they stay on the team as an admin.
 */
class TransferTeamOwnership
{
    public function transfer(User $owner, Team $team, User $newOwner): void
    {
        if (! $owner->ownsTeam($team) || $newOwner->is($owner)) {
            throw new AuthorizationException;
        }

        // Saving the team fires TeamUpdated, which syncs the organisation.
        DB::transaction(function () use ($owner, $team, $newOwner) {
            // Checked under lock: the new owner may be deleting their account
            // or leaving the team at this moment.
            $stillThere = User::whereKey($newOwner->id)->lockForUpdate()->exists();
            $role = DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', $newOwner->id)
                ->lockForUpdate()
                ->value('role');
            if (! $stillThere || $role !== 'admin') {
                throw new AuthorizationException;
            }

            // Jetstream keeps the owner out of the membership table.
            $team->users()->detach($newOwner);
            $team->users()->attach($owner, ['role' => 'admin']);

            // A team handed over is nobody's personal team.
            $team->forceFill([
                'user_id' => $newOwner->id,
                'personal_team' => false,
            ])->save();
        });
    }
}
