<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\GuidelinesInterface;
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

    public function resetForm()
    {
        $this->resetErrorBag();

        $this->mountCategories($this->team, GuidelinesInterface::DISABLED_CATEGORIES_STYLE);
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
}
