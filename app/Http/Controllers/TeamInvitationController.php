<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToHubSpot;
use App\Jobs\SyncUserToNlpApi;
use App\Jobs\SyncToPosthog;
use App\Models\TeamInvitation as ModelsTeamInvitation;
use Laravel\Jetstream\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController as BaseTeamInvitationController;

class TeamInvitationController extends BaseTeamInvitationController
{
    /**
     * Accept a team invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $invitation
     * @return \Illuminate\Http\Response
     */
    public function acceptSigned(Request $request, $invitation)
    {
        $user = $request->user();

        if ($user) {
            return $this->accept($request, $invitation);
        }

        $invitation = $this->getInvitiation($invitation);
        if ($invitation instanceof Response) {
            return $invitation;
        }

        $invitation->accepted = true;
        $invitation->save();

        return redirect()->route('register');
    }

    /**
     * Accept a team invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $invitation
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $invitation = null)
    {
        $invitation = $this->getInvitiation($invitation);
        if ($invitation instanceof Response) {
            return $invitation;
        }

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
     * @param  integer  $invitation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $invitation)
    {
        $invitation = $this->getInvitiation($invitation);
        if ($invitation instanceof Response) {
            return $invitation;
        }

        if (strtolower($request->user()->email) !== strtolower($invitation->email)) {
            abort(403, 'Unauthorized action.');
        }

        $invitation->delete();

        dispatch(new SyncUserToHubSpot($invitation->team->owner));
        dispatch(new SyncToPosthog($invitation->team->owner));

        // update notification count
        dispatch(new SyncUserToNlpApi($request->user(), 'high'));

        return back(303);
    }

    protected function getInvitiation($invitation)
    {
        $invitation = ModelsTeamInvitation::find($invitation);
        if (!$invitation) {
            return response()->view('errors.404', ['message' => __('teams.invitation_removed')], 404);
        }

        return $invitation;
    }
}
