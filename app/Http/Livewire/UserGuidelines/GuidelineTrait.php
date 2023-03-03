<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Support\Facades\Auth;

trait GuidelineTrait
{
    use HelpHeroTrait;

    protected function mountAttribute($user, $languageGuidelines, $attribute, $section = null)
    {
        if (LanguageGuidelines::isForcedOnTeam($user, $section ?? $attribute)) {
            $teamGuidelines = LanguageGuidelines::getTeamGuidelines($user);

            return $teamGuidelines->{$attribute};
        }

        return $languageGuidelines->{$attribute};
    }

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->user = Auth::user();

        $this->resetForm();
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function saved()
    {
        $this->mount();
    }

    protected function getLanguageGuidelines($user)
    {
        return LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
    }
}
