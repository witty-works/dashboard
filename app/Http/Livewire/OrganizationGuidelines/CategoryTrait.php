<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use Illuminate\Support\Facades\Auth;

trait CategoryTrait
{
    public function mountCategories($team, $categories)
    {
        $this->team = $team;

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $this->disabled_categories = $organizationRule->disabled_categories;
        foreach ($categories as $category) {
            $property = "disabled_categories_" . $category;
            $this->$property = !in_array($category, $this->disabled_categories);
        }
    }

    public function updateCategories($categories)
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        foreach ($categories as $category) {
            $property = "disabled_categories_" . $category;
            if (!$this->$property) {
                $this->disabled_categories[] = $category;
            } elseif (array_search($category, $this->disabled_categories) !== false) {
                unset($this->disabled_categories[array_search($category, $this->disabled_categories)]);
            }
        }

        $this->disabled_categories = array_values(array_unique($this->disabled_categories));
        $organizationRule->disabled_categories = $this->disabled_categories;

        $organizationRule->save();

        $this->emit('saved');
    }
}
