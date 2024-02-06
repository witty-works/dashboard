<?php

namespace App\Livewire;

use App\Helpers\Hubspot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Onboarding extends Component
{
    use AuthorizesRequests;

    public $showingModal = false;
    public $user;
    public $users;
    public $request_invite;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($user)
    {
        $this->users = null;
        $this->request_invite = null;
        $this->showingModal = false;
        $this->user = $user;
        if (!$user) {
            return;
        }

        $this->showingModal = !$user->hasCompletedOnboarding();

        if (
            !$user->invitations->count()
            && (!$user->currentTeam
                || $user->currentTeam->getTotalUserCount() <= 1
            )
        ) {
            try {
                $hubspot = new Hubspot();
                $company = $hubspot->findCompanyByUser($user);
            } catch (\Exception $e) {
            }

            if (!empty($company)) {
                if (!empty($company['properties']['name'])) {
                    $user->currentTeam->name = $company['properties']['name'];
                    $user->currentTeam->save();
                }

                $this->users = $user->getOrganizationUsers();
                // check for organization users that own teams
                $this->request_invite = (bool)$user->getOrganizationUsers(true)->count();
            }
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.onboarding');
    }
}
