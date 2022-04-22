<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Orthography extends Component
{
    use AuthorizesRequests;
    use CategoryTrait;

    public $disabled_categories;
    public $disabled_categories_force;
    public $disabled_categories_orthography;
    public $disabled_categories_orthography_force;

    protected $rules = [
        'disabled_categories_orthography' => 'nullable|boolean',
        'disabled_categories_orthography_force' => 'nullable|boolean',
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
        $this->mountCategories($team, OrganizationGuidelines::DISABLED_CATEGORIES_ORTHOGRAPHY);
    }

    public function updateOrganizationGuidelinesOrthography()
    {
        return $this->updateCategories(OrganizationGuidelines::DISABLED_CATEGORIES_ORTHOGRAPHY);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-guidelines.orthography');
    }

    protected function getOrganizationGuidelines($team)
    {
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
