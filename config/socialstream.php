<?php

use JoelButcher\Socialstream\Features;

return [
    'prompt' => 'Or Login Via',

    'home' => '/',

    'redirects' => [
        'login' => '/',
        'register' => '/',
        'login-failed' => '/login',
        'registration-failed' => '/register',
        'provider-linked' => '/user/profile',
        'provider-link-failed' => '/user/profile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Socialstream Route Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Socialstream will assign to the
    | routes that it registers with the application. When necessary, you may
    | modify these middleware; however, this default value is usually sufficient.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Socialstream Providers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the providers your application supports for OAuth.
    | Out of the box, Socialstream provides support for all of the OAuth
    | providers that are supported by Laravel Socialite.
    |
    */

    'providers' => [
        // Microsoft Office login is handled separately via office-login routes
        // and is not exposed in the UI as it's only for Office Add-in users
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of Socialstreams's features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features or you can even remove all of these if you need to.
    |
    */

    'features' => [
        Features::createAccountOnFirstLogin(),
        Features::loginOnRegistration(),
        Features::rememberSession(),
        Features::providerAvatars(),
    ],
];
