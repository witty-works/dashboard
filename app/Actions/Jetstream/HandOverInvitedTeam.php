<?php

namespace App\Actions\Jetstream;

use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncToPosthog;
use App\Jobs\RevokeApiKeysInNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * Accepting an owner invitation: the team the superadmin set up becomes the
 * invitee's, and the superadmin leaves it entirely.
 */
class HandOverInvitedTeam
{
    public function hand(User $newOwner, TeamInvitation $invitation): Team
    {
        [$team, $formerOwner] = DB::transaction(function () use ($newOwner, $invitation) {
            // Re-read under lock: a second acceptance, or a revocation, may
            // have got there first.
            $invitation = TeamInvitation::whereKey($invitation->id)->lockForUpdate()->first();
            if (! $invitation || ! $invitation->as_owner) {
                throw new AuthorizationException('This invitation is no longer valid.');
            }

            $team = Team::whereKey($invitation->team_id)->lockForUpdate()->firstOrFail();
            User::whereKey($newOwner->id)->lockForUpdate()->first();
            $formerOwner = User::find($team->user_id);

            if (! $formerOwner?->can(config('admin.permission'))) {
                throw new AuthorizationException('Only a team a superadmin owns can be handed over.');
            }
            if ($formerOwner->is($newOwner)) {
                throw new AuthorizationException('The invitee already owns this team.');
            }

            $team->users()->detach([$newOwner->id, $formerOwner->id]);
            $team->forceFill(['user_id' => $newOwner->id, 'personal_team' => false])->save();

            foreach ([$formerOwner] as $user) {
                $changes = [];
                if ((int) $user->current_team_id === $team->id) {
                    $changes['current_team_id'] = null;
                }
                if ((int) $user->license_team_id === $team->id) {
                    $changes['license_team_id'] = null;
                }
                if ($changes) {
                    $user->forceFill($changes)->save();
                }
            }

            $newOwner->refresh();
            $newOwner->forceFill([
                'current_team_id' => $team->id,
                'license_team_id' => $newOwner->license_team_id ?? $team->id,
            ])->save();

            // This invitation and any other owner invitation for the team.
            $team->teamInvitations()->where('as_owner', true)->delete();

            return [$team, $formerOwner];
        });

        DB::afterCommit(function () use ($team, $newOwner, $formerOwner) {
            dispatch(new SyncUserToNlpApi($newOwner, 'high'));
            dispatch(new SyncUserToNlpApi($formerOwner, 'high'));
            dispatch(new RevokeApiKeysInNlpApi($formerOwner->email, $team->posthogId()));
            dispatch(new SyncOrganizationToNlpApi($team, 'high'));
            dispatch(new SyncToPosthog($team));
        });

        return $team;
    }
}
