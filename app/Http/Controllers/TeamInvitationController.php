<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToHubSpot;
use App\Jobs\SyncUserToNlpApi;
use App\Jobs\SyncToPosthog;
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
    public function acceptSigned(Request $request, TeamInvitation $invitation)
    {
        $user = $request->user();
        if ($user) {
            return $this->accept($request, $invitation);
        }

        $invitation->accepted = true;
        $invitation->save();

        return redirect(config('fortify.home'));
    }

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

        $currentTeam = $user->currentTeam;
        if ($currentTeam) {
            if ($user->ownsTeam($currentTeam)) {
                if (
                    $currentTeam->subscribed()
                    && !$currentTeam->subscription()->canceled()
                ) {
                    $currentTeam->subscription()->cancel();
                }
            } else {
                $currentTeam->removeUser($user);
            }
        }

        parent::accept($request, $invitation);

        $user->switchTeam($invitation->team);

        // update notification count
        dispatch(new SyncUserToNlpApi($invitation->team->owner, 'high'));

        foreach ($user->invitations as $otherInvitation) {
            if ($invitation->id !== $otherInvitation->id) {
                continue;
            }

            $otherInvitation->delete();

            dispatch(new SyncUserToHubSpot($otherInvitation->team->owner));
            dispatch(new SyncToPosthog($otherInvitation->team->owner));

            // update notification count
            dispatch(new SyncUserToNlpApi($otherInvitation->team->owner, 'high'));
        }

        return redirect(config('fortify.home'))
            ->banner(__('teams.accepted_invitation', ['team' => $invitation->team->name]));
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

        dispatch(new SyncUserToHubSpot($invitation->team->owner));
        dispatch(new SyncToPosthog($invitation->team->owner));

        // update notification count
        dispatch(new SyncUserToNlpApi($request->user(), 'high'));

        return back(303);
    }
}
