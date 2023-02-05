<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GuestLayout extends Component
{
    public $pagetitle;

    public function __construct($pagetitle = null)
    {
        $this->pagetitle = $pagetitle;
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.guest');
    }
}
