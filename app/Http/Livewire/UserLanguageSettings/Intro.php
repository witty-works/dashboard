<?php

namespace App\Http\Livewire\UserLanguageSettings;

use App\Http\Livewire\UserGuidelineTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Intro extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    public $model;

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-language-settings.intro');
    }

    public function resetForm()
    {
    }
}
