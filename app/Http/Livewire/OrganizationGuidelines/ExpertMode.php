<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExpertMode extends Component
{
    use AuthorizesRequests;

    public $expert_mode;
    public $expert_mode_force;

    protected $rules = [
        'expert_mode' => 'nullable|boolean',
        'expert_mode_force' => 'nullable|boolean',
    ];

    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $this->expert_mode = (bool) $organizationRule->expert_mode;
        $this->expert_mode_force = (bool) $organizationRule->expert_mode_force;
    }

    public function updateOrganizationGuidelinesExpertMode()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $organizationRule->expert_mode = (bool) $this->expert_mode;
        $organizationRule->expert_mode_force = (bool) $this->expert_mode_force;

        $organizationRule->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-guidelines.expert-mode');
    }

    protected function getOrganizationGuidelines($team)
    {
        $organizationRule = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        if (!$this->team->subscribed()) {
            $this->expert_mode = false;
            $organizationRule->expert_mode = false;
            $this->expert_mode_force = false;
            $organizationRule->expert_mode_force = false;
        }

        return $organizationRule;
    }
}
