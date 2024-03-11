<?php

namespace App\Livewire\Teams;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LicenseManagement extends Component
{
    use AuthorizesRequests;

    public $licenses;
    public $assignedCount;
    public $team;

    protected $rules = [
        'licenses' => 'array',
    ];

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.teams.license_management');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->licenses = [];
        foreach ($this->team->allUsers() as $user) {
            if ($user->isUserLicensedToTeam($this->team)) {
                $this->licenses[$user->id] = true;
            }
        }

        $this->updateAssignedCount();
    }

    public function updateAssignedCount()
    {
        $this->assignedCount = count(array_filter($this->licenses));
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function updateLicenses()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $this->updateAssignedCount();
        if ($this->team->getUserLicensesCount() < $this->assignedCount) {
            $params = [
                'license_count' => $this->team->getUserLicensesCount(),
                'assigned_count' => $this->assignedCount
            ];
            $message = __('teams.license_limit_reached_error', $params);
            throw ValidationException::withMessages([$message]);
        }

        foreach ($this->team->allUsers() as $user) {
            if (!empty($this->licenses[$user->id])) {
                if ($user->license_team_id === null) {
                    $user->license_team_id = $this->team->id;
                    $user->save();
                } elseif (!$user->isUserLicensedToTeam($this->team)) {
                    // @TODO cannot assign license to a user that already has a license
                }
            } elseif ($user->isUserLicensedToTeam($this->team)) {
                $user->license_team_id = null;
                $user->save();
            }
        }

        $this->dispatch('saved');
    }
}
