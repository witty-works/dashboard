<?php

namespace App\Livewire\Teams;

use App\Livewire\HelpHeroTrait;
use App\Jobs\SyncUserToNlpApi;
use App\Models\TeamInvitationRequest;
use App\Mail\TeamInvitationRequestAccepted;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager as TeamMemberManagerBase;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;
use Livewire\Attributes\Locked;

/**
 * Rendered for admins on the team page and, so that they can leave, for
 * members without admin rights on their profile page. The view hides what
 * those members may not do, but every action can be called from the browser,
 * so each one checks the permission itself.
 */
class TeamMemberManagerHelpHero extends TeamMemberManagerBase
{
    use HelpHeroTrait;

    /** The team being managed: the server's, never the browser's to change. */
    #[Locked]
    public $team;

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
        $invitationRequest = TeamInvitationRequest::select('team_invitation_requests.*')
            ->join('users', 'users.id', '=', 'team_invitation_requests.user_id')
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
        Gate::authorize('removeTeamMember', $this->team);

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
        Gate::authorize('removeTeamMember', $this->team);

        if (!empty($invitationRequest)) {
            abort_unless((int) $invitationRequest->team_id === (int) $this->team->id, 403);

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
        Gate::authorize('addTeamMember', $this->team);

        if (empty($invitationRequest)) {
            session()->flash('teams_invitation_request_message', __('content.invitation_request_already_accepted'));

            return;
        }

        abort_unless((int) $invitationRequest->team_id === (int) $this->team->id, 403);

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
