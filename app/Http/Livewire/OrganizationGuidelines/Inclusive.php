<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        $this->team = $team;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->resetErrorBag();

        $this->mountCategories($this->team, GuidelinesInterface::DISABLED_CATEGORIES_INCLUSIVE);
    }

    public function updateLanguageGuidelinesInclusive()
    {
        return $this->updateCategories(GuidelinesInterface::DISABLED_CATEGORIES_INCLUSIVE);
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

    protected function getLanguageGuidelines($team)
    {
        return LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
