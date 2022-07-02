<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\LanguageGuidelines;

trait AttributeTrait
{
    protected function mountAttribute($user, $languageGuidelines, $attribute, $section = null)
    {
        if (LanguageGuidelines::isForcedOnTeam($user, $section ?? $attribute)) {
            $teamGuidelines = LanguageGuidelines::getTeamGuidelines($user);

            return $teamGuidelines->{$attribute};
        }

        return $languageGuidelines->{$attribute};
    }
}
