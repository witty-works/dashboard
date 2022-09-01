<?php

namespace App\Http\Livewire\UserGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Intro extends Component
{
    use AuthorizesRequests;

    public $user;

    protected $listeners = ['saved'];

    /**
     * Mount the component.
     *
     * @param  mixed  $user
     * @return void
     */
    public function mount($user)
    {
        $this->user = $user;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-guidelines.intro');
    }

    public function saved()
    {
        return $this->render();
    }
}
