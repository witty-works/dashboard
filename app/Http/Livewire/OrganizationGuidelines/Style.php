<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        $this->mountCategories($team, GuidelinesInterface::DISABLED_CATEGORIES_STYLE);
    }

    public function updateLanguageGuidelinesStyle()
    {
        return $this->updateCategories(GuidelinesInterface::DISABLED_CATEGORIES_STYLE);
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

    protected function getLanguageGuidelines($team)
    {
        return LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
