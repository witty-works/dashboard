<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Style extends Component
{
    use AuthorizesRequests;

    public $disabled_categories;
    public $disabled_categories_style;

    protected $rules = [
        'disabled_categories_style' => 'nullable|boolean',
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

        $this->disabled_categories = (array) json_decode($organizationRule->disabled_categories, JSON_OBJECT_AS_ARRAY);
        foreach (OrganizationGuidelines::DISABLED_CATEGORIES_STYLE as $category) {
            $property = "disabled_categories_" . $category;
            $this->$property = in_array($category, $this->disabled_categories);
        }
    }

    public function updateOrganizationGuidelinesStyle()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        foreach (OrganizationGuidelines::DISABLED_CATEGORIES_STYLE as $category) {
            $property = "disabled_categories_" . $category;
            if ($this->$property) {
                $this->disabled_categories[] = $category;
            } elseif (array_search($category, $this->disabled_categories) !== false) {
                unset($this->disabled_categories[array_search($category, $this->disabled_categories)]);
            }
        }

        $this->disabled_categories = array_values(array_unique($this->disabled_categories));
        $organizationRule->disabled_categories = json_encode($this->disabled_categories);

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
        return view('livewire.organization-guidelines.style');
    }

    protected function getOrganizationGuidelines($team)
    {
        return OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
