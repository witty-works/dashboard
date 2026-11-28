<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'microsoft_office' => [
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'redirect_uri' => env('MICROSOFT_CLIENT_REDIRECT_URI'),
    ],
];
