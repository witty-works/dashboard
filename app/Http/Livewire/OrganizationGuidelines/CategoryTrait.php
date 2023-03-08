<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Jobs\SyncOrganizationToNlpApi;
use App\Models\LanguageGuidelines;
use Illuminate\Support\Facades\Auth;

trait CategoryTrait
{
    use GuidelineTrait;

    public function mountCategories($team, $categories)
    {
        $this->team = $team;

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $this->disabled_categories = (array) $languageGuidelines->disabled_categories;
        $this->disabled_categories_force = (array) $languageGuidelines->disabled_categories_force;

        foreach ($categories as $category) {
            $property = "disabled_categories_$category";
            $this->$property = !in_array($category, $this->disabled_categories);

            $property = "disabled_categories_force_$category";
            $this->$property = in_array($category, $this->disabled_categories_force);
        }
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function updateCategories($categories)
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $disabled_categories = [];
        $disabled_categories_force = [];
        foreach ($categories as $category) {
            $property = "disabled_categories_$category";

            $languageGuidelines->inPlaceUpateArray($category, 'disabled_categories', $this->$property);

            if (!$this->$property) {
                $disabled_categories[] = $category;
            }

            $property = "disabled_categories_force_$category";

            $languageGuidelines->inPlaceUpateArray($category, 'disabled_categories_force', !$this->$property);

            if (!$this->team->subscribed() || $this->$property) {
                $disabled_categories_force[] = $category;
            }
        }

        $this->disabled_categories = $disabled_categories;
        $this->disabled_categories_force = $disabled_categories_force;

        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->emit('saved');
        $this->updateHelpHero();

        dispatch(new SyncOrganizationToNlpApi($this->team, 'high'));
    }
}
