<?php

namespace App\View\Components;

class PlainLayout extends AppLayout
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.plain');
    }
}
