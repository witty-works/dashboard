<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;

trait HelpHeroTrait
{
    protected function updateHelpHero()
    {
        $data = [
            'helpHeroData' => Auth::user()->getHubspotData()
        ];

        $this->dispatch('helpHeroUpdate', $data);
    }
}
