<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsController extends Controller
{
    /**
     * Display the terms of service page.
     */
    public function show()
    {
        return view('terms');
    }
}
