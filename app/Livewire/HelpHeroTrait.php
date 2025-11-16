<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;

trait HelpHeroTrait
{
    protected function updateHelpHero()
    {
        $this->dispatch('helpHeroUpdate', helpHeroData: Auth::user()->getUserData());
    }
}
