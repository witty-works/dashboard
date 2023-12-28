<?php

namespace App\Livewire;

use App\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;

trait UserGuidelineTrait
{
    use HelpHeroTrait;
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
