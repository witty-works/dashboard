<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;

    public $german_gender_ending;
    public $german_gender_ending_force;
    public $gendered_roles_format;
    public $gendered_roles_format_force;

    protected $rules = [
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'german_gender_ending_force' => 'nullable|boolean',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender',
        'gendered_roles_format_force' => 'nullable|boolean',
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

        $this->german_gender_ending = $organizationRule->german_gender_ending;
        $this->german_gender_ending_force = $organizationRule->german_gender_ending_force;
        $this->gendered_roles_format = $organizationRule->gendered_roles_format;
        $this->gendered_roles_format_force = $organizationRule->gendered_roles_format_force;
    }

    public function updateOrganizationGuidelinesGerman()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $organizationRule->german_gender_ending = $this->german_gender_ending;
        $organizationRule->german_gender_ending_force = $this->german_gender_ending_force;
        $organizationRule->gendered_roles_format = $this->gendered_roles_format;
        $organizationRule->gendered_roles_format_force = $this->gendered_roles_format_force;

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
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
