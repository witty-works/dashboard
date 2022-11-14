<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;

trait HelpHeroTrait
{
    protected function updateHelpHero()
    {
        $data = [
            'helpHeroData' => Auth::user()->getHubspotData()
        ];

        $this->dispatchBrowserEvent('helpHeroUpdate', $data);
    }
}
