<?php

namespace App\Livewire\Teams;

use App\Livewire\HelpHeroTrait;
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
        $invitationRequest = TeamInvitationRequest::join('users', 'users.id', '=', 'team_invitation_requests.user_id')
            ->where('email', $this->addTeamMemberForm['email'])
            ->where('team_id', $this->team->id)
            ->first();

        if ($invitationRequest) {
            $this->acceptTeamInvitationRequest($invitationRequest, $this->addTeamMemberForm['role']);

            $this->addTeamMemberForm = [
                'email' => '',
                'role' => null,
            ];

            $this->team = $this->team->fresh();
        } else {
            parent::addTeamMember();
        }

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
     * @param  TeamInvitationRequest  $invitationRequest
     * @return void
     */
    public function cancelTeamInvitationRequest(?TeamInvitationRequest $invitationRequest = null)
    {
        if (!empty($invitationRequest)) {
            $invitationRequest->delete();
        }

        $this->team = $this->team->fresh();

        $this->updateHelpHero();
    }

    /**
     * Accept a pending team member invitation request.
     *
     * @param  TeamInvitationRequest  $invitationRequest
     * @return void
     */
    public function acceptTeamInvitationRequest(?TeamInvitationRequest $invitationRequest = null, $role = 'user')
    {
        if (empty($invitationRequest)) {
            session()->flash('teams_invitation_request_message', __('content.invitation_request_already_accepted'));

            return;
        }

        if ($this->team->id != $invitationRequest->team->id) {
            return;
        }

        $user = $invitationRequest->user;

        app(AddsTeamMembers::class)->add(
            $this->team->owner,
            $this->team,
            $user->email,
            $role,
        );

        $user->switchTeam($this->team);

        $this->team = $this->team->fresh();

        Mail::to($user->email)->send(
            new TeamInvitationRequestAccepted($invitationRequest, $role, request()->user())
        );

        $invitationRequests = TeamInvitationRequest::where('user_id', $user->id)->get();
        foreach ($invitationRequests as $invitationRequest) {
            $invitationRequest->delete();

            // update notification count
            dispatch(new SyncUserToNlpApi($invitationRequest->team->owner, 'high'));
        }

        $this->dispatch('saved');

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
            'role' => null,
        ];
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
