<?php

namespace App\Http\Livewire\Teams;

use App\Http\Livewire\HelpHeroTrait;
use App\Jobs\SyncUserToNlpApi;
use App\Models\TeamInvitationRequest;
use App\Mail\TeamInvitationRequestAccepted;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager as TeamMemberManagerBase;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;

class TeamMemberManagerHelpHero extends TeamMemberManagerBase
{
    use HelpHeroTrait;

    public function mount($team)
    {
        parent::mount($team);

        $this->resetForm();
    }

    /**
     * Add a new team member to a team.
     *
     * @return void
     */
    public function addTeamMember()
    {
        parent::addTeamMember();
        $this->updateHelpHero();
    }

    /**
     * Cancel a pending team member invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function cancelTeamInvitation($invitationId)
    {
        parent::cancelTeamInvitation($invitationId);
        $this->updateHelpHero();
    }

    /**
     * Cancel a pending team member invitation request.
     *
     * @param  int  $invitationRequestId
     * @return void
     */
    public function cancelTeamInvitationRequest($invitationRequestId)
    {
        if (!empty($invitationRequestId)) {
            TeamInvitationRequest::whereKey($invitationRequestId)->delete();
        }

        $this->team = $this->team->fresh();

        $this->updateHelpHero();
    }

    /**
     * Accept a pending team member invitation request.
     *
     * @param  int  $invitationRequestId
     * @return void
     */
    public function acceptTeamInvitationRequest($invitationRequestId)
    {
        if (empty($invitationRequestId)) {
            return;
        }

        $invitationRequest = TeamInvitationRequest::firstWhere('id', $invitationRequestId);
        if (empty($invitationRequest)) {
            session()->flash('teams_invitation_request_message', __('content.invitation_request_already_accepted'));

            return;
        }

        if (
            $invitationRequest->team->getUserLicensesLimitReached()
            || $this->team->id != $invitationRequest->team->id
        ) {
            return;
        }

        $user = $invitationRequest->user;

        app(AddsTeamMembers::class)->add(
            $this->team->owner,
            $this->team,
            $user->email,
            $this->team->subscribed() ? 'user' : 'admin'
        );

        $user->switchTeam($this->team);

        $this->team = $this->team->fresh();

        Mail::to($user->email)->send(new TeamInvitationRequestAccepted($invitationRequest));

        $invitationRequests = TeamInvitationRequest::where('user_id', $user->id)->get();
        foreach ($invitationRequests as $invitationRequest) {
            $invitationRequest->delete();

            // update notification count
            dispatch(new SyncUserToNlpApi($invitationRequest->team->owner, 'high'));
        }

        $this->emit('saved');

        $this->updateHelpHero();
    }

    /**
     * Remove the currently authenticated user from the team.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesTeamMembers  $remover
     * @return void
     */
    public function leaveTeam(RemovesTeamMembers $remover)
    {
        parent::leaveTeam($remover);

        $this->updateHelpHero();
    }

    /**
     * Remove a team member from the team.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesTeamMembers  $remover
     * @return void
     */
    public function removeTeamMember(RemovesTeamMembers $remover)
    {
        parent::removeTeamMember($remover);

        $this->updateHelpHero();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->addTeamMemberForm = [
            'email' => '',
            'role' => $this->team->subscribed() ? null : 'admin',
        ];
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
