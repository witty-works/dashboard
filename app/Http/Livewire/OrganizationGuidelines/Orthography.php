<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\GuidelinesInterface;
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

    public function resetForm()
    {
        $this->resetErrorBag();

        $this->mountCategories($this->team, GuidelinesInterface::DISABLED_CATEGORIES_ORTHOGRAPHY);
    }

    public function updateLanguageGuidelinesOrthography()
    {
        return $this->updateCategories(GuidelinesInterface::DISABLED_CATEGORIES_ORTHOGRAPHY);
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
}
