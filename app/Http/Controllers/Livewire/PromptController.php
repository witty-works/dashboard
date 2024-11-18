<?php

namespace App\Http\Controllers\Livewire;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    public function show()
    {
        if (!Auth::user()->hasRole('Superadmin')) {
            abort(404);
        }

        return view('prompt');
    }
}
