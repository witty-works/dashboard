<?php

namespace App\Http\Livewire\Teams;

use App\Http\Livewire\HelpHeroTrait;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager as TeamMemberManagerBase;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;

class TeamMemberManagerHelpHero extends TeamMemberManagerBase
{
    use HelpHeroTrait;

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
}
