<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function show()
    {
        return view('policy');
    }
}
