<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToHubSpot;
use App\Jobs\SyncUserToNlpApi;
use App\Jobs\SyncUserToPosthog;
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
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            abort(403, 'Unauthorized action.');
        }

        $response = parent::accept($request, $invitation);

        $currentTeam = $user->currentTeam;
        if ($currentTeam) {
            if ($user->ownsTeam($currentTeam)) {
                if ($currentTeam->subscribed() && !$currentTeam->subscription()->canceled()) {
                    $currentTeam->subscription()->cancel();
                }
            } else {
                $currentTeam->removeUser($user);
            }
        }

        $user->switchTeam($invitation->team);

        foreach ($user->invitations as $invitation) {
            dispatch(new SyncUserToHubSpot($invitation->team->owner));
            dispatch(new SyncUserToPosthog($invitation->team->owner));

            $invitation->delete();
        }

        // update notification count
        dispatch(new SyncUserToNlpApi($user, 'high'));

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

        dispatch(new SyncUserToHubSpot($invitation->team->owner));
        dispatch(new SyncUserToPosthog($invitation->team->owner));

        $invitation->delete();

        // update notification count
        dispatch(new SyncUserToNlpApi($request->user(), 'high'));

        return back(303);
    }
}
