<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Style extends Component
{
    use AuthorizesRequests;
    use CategoryTrait;

    public $disabled_categories;
    public $disabled_categories_force;
    public $disabled_categories_style;
    public $disabled_categories_style_force;

    protected $rules = [
        'disabled_categories_style' => 'nullable|boolean',
        'disabled_categories_style_force' => 'nullable|boolean',
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
        $this->mountCategories($team, OrganizationGuidelines::DISABLED_CATEGORIES_STYLE);
    }

    public function updateOrganizationGuidelinesStyle()
    {
        return $this->updateCategories(OrganizationGuidelines::DISABLED_CATEGORIES_STYLE);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-guidelines.style');
    }

    protected function getOrganizationGuidelines($team)
    {
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
