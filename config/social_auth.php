<?php

/*
|--------------------------------------------------------------------------
| Social Authentication
|--------------------------------------------------------------------------
|
| This file used to configure joelbutcher/socialstream. That package was
| archived upstream in December 2025 and never supported Laravel 13, so it was
| removed and the pieces this application actually used were absorbed into
| App\ (see App\Providers\SocialAuthServiceProvider).
|
| Generic OAuth login was never enabled here — the providers list has always
| been empty. Microsoft Office SSO is handled separately by
| App\Http\Controllers\OAuthController via the office-login routes.
|
*/

return [
    'home' => '/',

    'redirects' => [
        'login' => '/',
        'register' => '/',
        'login-failed' => '/login',
        'registration-failed' => '/register',
        'provider-linked' => '/user/profile',
        'provider-link-failed' => '/user/profile',
    ],

    'middleware' => ['web'],

    /*
    | Whether a session established through SSO is remembered. Replaces the
    | package's Features::rememberSession().
    */
    'remember_session' => true,
];
