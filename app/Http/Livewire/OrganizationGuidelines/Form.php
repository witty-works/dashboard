<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $german_gender_ending;
    public $gendered_roles_format;
    public $store_context;

    protected $rules = [
        'german_gender_ending' => 'nullable|string',
        'gendered_roles_format' => 'nullable|string',
        'store_context' => 'nullable|boolean',
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
        $this->gendered_roles_format = $organizationRule->gendered_roles_format;
        $this->store_context = (bool) $organizationRule->store_context;
    }

    public function updateOrganizationGuidelines()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $organizationRule->german_gender_ending = $this->german_gender_ending;
        $organizationRule->gendered_roles_format = $this->gendered_roles_format;
        $organizationRule->store_context = (bool) $this->store_context;

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
        return view('livewire.organization-guidelines.form');
    }

    protected function getOrganizationGuidelines($team)
    {
        $organizationRule = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        return $organizationRule;
    }
}
