<?php

namespace App\Livewire\Teams;

use App\Actions\Jetstream\TransferTeamOwnership;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager as TeamMemberManagerBase;
use Livewire\Attributes\Locked;

/**
 * Rendered for admins on the team page and, so that they can leave, for
 * members without admin rights on their profile page. The view hides what
 * those members may not do, but every action can be called from the browser,
 * so each one checks the permission itself.
 */
class TeamMemberManagerHelpHero extends TeamMemberManagerBase
{
    /** The team being managed: the server's, never the browser's to change. */
    #[Locked]
    public $team;

    /** Whether the hand-over of the team is being confirmed, and to whom. */
    public $confirmingOwnershipTransfer = false;

    public $ownershipTransferTo = null;

    public function mount($team)
    {
        parent::mount($team);

        $this->resetForm();
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
    }

    /**
     * Ask the owner to confirm handing the team over to the given admin.
     *
     * @param  int  $userId
     * @return void
     */
    public function confirmOwnershipTransfer($userId)
    {
        $this->ownershipTransferTo = (int) $userId;
        $this->confirmingOwnershipTransfer = true;
    }

    /**
     * Hand the team over. TransferTeamOwnership checks that the signed-in
     * user owns the team and that the new owner is one of its admins.
     *
     * @return void
     */
    public function transferOwnership(TransferTeamOwnership $transfer)
    {
        $newOwner = User::find($this->ownershipTransferTo);
        abort_unless($newOwner instanceof User, 403);

        $transfer->transfer($this->user, $this->team, $newOwner);

        $this->confirmingOwnershipTransfer = false;
        $this->ownershipTransferTo = null;
        $this->team = $this->team->fresh();

        $this->dispatch('saved');
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
