<?php

namespace App\Actions\Admin;

use App\Mail\TeamInvitation as TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * A superadmin invites the future owner of a team they set up. At most one
 * owner invitation per team, and only while a superadmin owns the team:
 * accepting it takes the team from its current owner.
 */
class InviteTeamOwner
{
    public function invite(User $superadmin, Team $team, string $email): TeamInvitation
    {
        abort_unless($superadmin->can(config('admin.permission')), 403);

        $email = strtolower(trim($email));
        Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->validate();

        $invitation = DB::transaction(function () use ($team, $email) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();

            if (! $team->owner?->can(config('admin.permission'))) {
                throw ValidationException::withMessages(['email' => __('content.admin_owner_invitation_not_superadmin_team')]);
            }
            if ($team->teamInvitations()->where('as_owner', true)->exists()) {
                throw ValidationException::withMessages(['email' => __('content.admin_owner_invitation_exists')]);
            }
            if ($team->teamInvitations()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                throw ValidationException::withMessages(['email' => __('content.admin_invitation_exists_for_address')]);
            }

            $invitation = new TeamInvitation(['email' => $email, 'role' => 'admin']);
            $invitation->team()->associate($team);
            $invitation->forceFill(['as_owner' => true])->save();

            return $invitation;
        });

        Mail::to($email)->send(new TeamInvitationMail($invitation));

        return $invitation;
    }
}
