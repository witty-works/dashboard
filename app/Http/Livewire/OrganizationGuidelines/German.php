<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;

    public $german_rules_force;
    public $german_gender_ending;
    public $gendered_roles_format;

    protected $rules = [
        'german_rules_force' => 'nullable|boolean',
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
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

        $this->german_rules_force = $organizationRule->german_rules_force;
        $this->german_gender_ending = $organizationRule->german_gender_ending;
        $this->gendered_roles_format = $organizationRule->gendered_roles_format;
    }

    public function updateOrganizationGuidelinesGerman()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $organizationRule->german_rules_force = $this->german_rules_force;
        $organizationRule->german_gender_ending = $this->german_gender_ending;
        $organizationRule->gendered_roles_format = $this->gendered_roles_format;

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
        return view('livewire.organization-guidelines.german');
    }

    protected function getOrganizationGuidelines($team)
    {
        $organizationRule = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        if (!$this->team->subscribed()) {
            $this->gendered_roles_format = 'both';
            $organizationRule->gendered_roles_format = 'both';
        }

        return $organizationRule;
    }
}
