<?php

namespace App\Http\Controllers;

use App\Support\LegalDocument;

class PolicyController extends Controller
{
    /**
     * Display the deployment's privacy policy (config app.privacy_policy_file).
     */
    public function show()
    {
        return view('policy', ['content' => LegalDocument::html(config('app.privacy_policy_file'))]);
    }
}
