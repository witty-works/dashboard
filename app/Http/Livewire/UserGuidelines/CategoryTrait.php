<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;

trait CategoryTrait
{
    protected function mountAttribute($user, $languageGuidelines)
    {
        $teamGuidelines = LanguageGuidelines::getTeamGuidelines($user);
        $disabled_categories = [];

        foreach (GuidelinesInterface::DISABLED_CATEGORIES as $category) {
            if (LanguageGuidelines::isForcedOnTeam($user, $category)) {
                if (in_array($category, (array) $teamGuidelines->disabled_categories)) {
                    $disabled_categories[] = $category;
                }
            } elseif (in_array($category, (array) $languageGuidelines->disabled_categories)) {
                $disabled_categories[] = $category;
            }
        }

        return $disabled_categories;
    }

    public function mountCategories($user, $categories)
    {
        $this->user = $user;

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->disabled_categories = $this->mountAttribute($this->user, $languageGuidelines);

        foreach ($categories as $category) {
            $property = "disabled_categories_" . $category;
            $this->$property = !in_array($category, $this->disabled_categories);
        }
    }

    public function updateCategories($categories)
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $disabled_categories = [];
        foreach ($categories as $category) {
            $property = "disabled_categories_" . $category;

            $languageGuidelines->inPlaceUpateArray($category, 'disabled_categories', $this->$property);

            if (!$this->$property) {
                $disabled_categories[] = $category;
            }
        }

        $this->disabled_categories = $disabled_categories;

        $this->emit('saved');
    }
}
