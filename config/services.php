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
            'register' => env('AADB2C_POLICY_REGISTER'),
            'profile' => env('AADB2C_POLICY_PROFILE'),
            'browser_login' => env('AADB2C_POLICY'),
            'browser_register' => env('AADB2C_POLICY_REGISTER'),
        ],
        'default_algorithm' => env('AADB2C_DefaultAlgorithm', 'RS256'), // decoding algorithm JWK key such as 'RS256'
        'redirect_uri' => explode(',', env('AADB2C_BROWSER_REDIRECT_URIS')),
        'validate_redirect_uri_disabled' => env('AADB2C_VALIDATE_EXTENSION_REDIRECT_URI_DISABLED', false)
    ],

    'microsoft_office' => [
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'redirect_uri' => env('MICROSOFT_CLIENT_REDIRECT_URI'),
    ],

    'mailjet' => [
        'key' => env('MAILJET_APIKEY'),
        'secret' => env('MAILJET_APISECRET'),
    ]
];
