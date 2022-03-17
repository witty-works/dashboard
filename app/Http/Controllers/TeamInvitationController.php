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

        $result = parent::accept($request, $invitation);

        $user->switchTeam($invitation->team);

        foreach ($user->invitations as $invitation) {
            $invitation->delete();
        }

        return $result;
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

        return parent::destroy($request, $invitation);
    }
}
