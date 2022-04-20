<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Language extends Component
{
    use AuthorizesRequests;

    public $preferred_variants;
    public $preferred_variants_force;
    public $preferred_variants_de;
    public $preferred_variants_en;

    protected $rules = [
        'preferred_variants_force' => 'nullable|boolean',
        'preferred_variants_de' => 'nullable|string|in:both,de-DE,de-AT,de-CH',
        'preferred_variants_en' => 'nullable|string|in:both,en-US,en-GB',
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

        $this->preferred_variants = (array) $organizationRule->preferred_variants;
        $this->preferred_variants_force = (bool) $organizationRule->preferred_variants_force;

        foreach (OrganizationGuidelines::LANGUAGES as $lang) {
            $property = "preferred_variants_" . $lang;
            foreach (constant('App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_' . strtoupper($lang)) as $locale => $trans_key) {
                if (in_array($locale, $this->preferred_variants)) {
                    $this->$property = $locale;
                }
            }
        }
    }

    public function updateOrganizationGuidelinesLanguage()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $this->preferred_variants = [];
        foreach (OrganizationGuidelines::LANGUAGES as $lang) {
            $property = "preferred_variants_" . $lang;
            if ($this->$property) {
                $this->preferred_variants[] = $this->$property;
            }
        }

        $organizationRule->preferred_variants = $this->preferred_variants;
        $organizationRule->preferred_variants_force = $this->preferred_variants_force;

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
        return view('livewire.organization-guidelines.language');
    }

    protected function getOrganizationGuidelines($team)
    {
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}