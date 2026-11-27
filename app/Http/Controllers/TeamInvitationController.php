<?php

namespace App\Http\Controllers;

use App\Actions\Jetstream\HandOverInvitedTeam;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
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

        // Matching the address is not enough: anyone can register with it.
        // Keep the acceptance for when its owner has verified it.
        if (! $user->hasVerifiedEmail()) {
            $invitation->accepted = true;
            $invitation->save();

            return redirect()->route('verification.notice');
        }

        // An invitation to own the team: it is handed over, not joined.
        if ($invitation->as_owner) {
            try {
                app(HandOverInvitedTeam::class)->hand($user, $invitation);
            } catch (AuthorizationException $e) {
                // No longer valid: dropped, rather than offered and refused
                // again on every page.
                Log::warning('An owner invitation could not be handed over', ['invitation' => $invitation->id, 'reason' => $e->getMessage()]);
                $invitation->delete();

                return redirect(config('fortify.home'))->dangerBanner(__('content.owner_invitation_no_longer_valid'));
            }

            return redirect(config('fortify.home'))
                ->banner(__('teams.accepted_invitation', ['team' => $invitation->team->name]));
        }

        parent::accept($request, $invitation);

        $user->switchTeam($invitation->team);

        // update notification count
        dispatch(new SyncUserToNlpApi($invitation->team->owner, 'high'));

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

        // Only once the address is verified: an account can claim any address.
        if (! $request->user()->hasVerifiedEmail()
            || strtolower($request->user()->email) !== strtolower($invitation->email)) {
            abort(403, 'Unauthorized action.');
        }

        $invitation->delete();

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
