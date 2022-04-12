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

    'azureadb2c' => [
        'client_id' => env('AADB2C_CLIENT_ID'),
        'client_secret' => env('AADB2C_CLIENT_SECRET'),
        'redirect' => '/oauth/azureadb2c/callback',
        'redirect_template' => '/oauth/azureadb2c/{policy}/callback',
        'scope' => env('AADB2C_ACCESS_TOKEN_SCOPES'),
        'domain' => env('AADB2C_DOMAIN'),
        'policy' => [
            'login' => env('AADB2C_POLICY'),
            'profile' => env('AADB2C_POLICY_PROFILE'),
            'register' => env('AADB2C_POLICY_REGISTER'),
            'browser_login' => env('AADB2C_POLICY'),
            'browser_register' => env('AADB2C_POLICY_REGISTER'),
        ],
        'redirect_uri' => explode(',', env('AADB2C_BROWSER_REDIRECT_URIS')),
    ],
];
