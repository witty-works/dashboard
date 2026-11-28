<?php

namespace App\Http\Controllers;

use App\Support\LegalDocument;

class TermsController extends Controller
{
    /**
     * Display the deployment's terms of service (config app.terms_of_service_file).
     */
    public function show()
    {
        return view('terms', ['content' => LegalDocument::html(config('app.terms_of_service_file'))]);
    }
}
