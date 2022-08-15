<?php

namespace App\Http\Controllers;

use Laravel\Jetstream\TeamInvitation;
use Illuminate\Http\Request;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController as BaseTeamInvitationController;

class TeamInvitationController extends BaseTeamInvitationController
{
    /**
     * Accept a team invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Jetstream\TeamInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, TeamInvitation $invitation)
    {
        $user = $request->user();
        if ($user->email !== $invitation->email) {
            abort(403, 'Unauthorized action.');
        }

        $response = parent::accept($request, $invitation);

        $user->switchTeam($invitation->team);

        if ($user->currentTeam) {
            if ($user->ownsTeam($user->currentTeam)) {
                $user->currentTeam->delete();
            } else {
                $user->currentTeam->removeUser($user);
            }
        }

        foreach ($user->invitations as $invitation) {
            $invitation->delete();
        }

        return $response->banner(__('teams.accepted_invitation', ['team' => $invitation->team->name]));
    }

    /**
     * Cancel the given team invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Jetstream\TeamInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, TeamInvitation $invitation)
    {
        if ($request->user()->email !== $invitation->email) {
            abort(403, 'Unauthorized action.');
        }

        $invitation->delete();

        return back(303);
    }
}
