<?php

namespace App\Livewire\Teams;

use App\Livewire\HelpHeroTrait;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('removeTeamMember', $this->team);

        parent::cancelTeamInvitation($invitationId);
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
