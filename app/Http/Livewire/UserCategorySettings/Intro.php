<?php

namespace App\Http\Livewire\UserCategorySettings;

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
        return view('livewire.user-category-settings.intro');
    }

    public function resetForm()
    {
    }
}
