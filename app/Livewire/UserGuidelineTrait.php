<?php

namespace App\Livewire;

use App\Models\LanguageGuidelines;

trait UserGuidelineTrait
{
    use TeamsGuidelineTrait;

    protected function mountAttribute($model, $languageGuidelines, $attribute, $section = null)
    {
        if (LanguageGuidelines::isForcedOnTeam($model, $section ?? $attribute)) {
            $teamGuidelines = LanguageGuidelines::getTeamGuidelines($model);

            return $teamGuidelines->{$attribute};
        }

        return $languageGuidelines->{$attribute};
    }
}
