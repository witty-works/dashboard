<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inclusive extends Component
{
    use AuthorizesRequests;
    use CategoryTrait;

    public $disabled_categories;
    public $disabled_categories_force;
    public $disabled_categories_inclusive;
    public $disabled_categories_inclusive_force;

    protected $rules = [
        'disabled_categories_inclusive' => 'nullable|boolean',
        'disabled_categories_inclusive_force' => 'nullable|boolean',
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
        $this->mountCategories($team, OrganizationGuidelines::DISABLED_CATEGORIES_INCLUSIVE);
    }

    public function updateOrganizationGuidelinesInclusive()
    {
        return $this->updateCategories(OrganizationGuidelines::DISABLED_CATEGORIES_INCLUSIVE);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-guidelines.inclusive');
    }

    protected function getOrganizationGuidelines($team)
    {
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
