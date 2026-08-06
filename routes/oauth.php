<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;

/*
|--------------------------------------------------------------------------
| OAuth2 Routes
|--------------------------------------------------------------------------
|
| Registered from RouteServiceProvider under the /oauth prefix and the
| passport.* name prefix, replacing the routes Passport would otherwise
| register itself — see App\Providers\AuthServiceProvider::register() for why.
| Keep the route names: Passport's own consent view posts to
| passport.authorizations.approve and .deny.
|
| Only authorization_code + PKCE and refresh_token are exposed. Clients are
| provisioned from the CLI (`php artisan passport:extension-client`), so there
| is deliberately no client-management API here.
|
*/

/*
 * The token endpoint. Three deliberate choices:
 *
 * - No `web` middleware. This is a cross-origin POST from the extension with
 *   neither cookies nor a CSRF token, and it authenticates by PKCE code
 *   verifier rather than by session.
 * - `throttle:auth`, the 10/min per-IP limiter from RouteServiceProvider,
 *   rather than the bare `throttle` (60/min) Passport ships with. This endpoint
 *   hands out credentials and is hit once per hour per user in normal use.
 * - Handles the refresh_token grant too, so there is no separate refresh route.
 *   The old /api/refresh-token is gone for good.
 */
Route::post('/token', [AccessTokenController::class, 'issueToken'])
    ->middleware('throttle:auth')
    ->name('token');

/*
 * The authorization endpoint is a browser navigation, so it does need the
 * session. AuthorizationController handles guests itself: it stashes
 * url.intended and redirects to the Fortify login screen, then comes back here
 * once the user has signed in.
 */
Route::middleware('web')->group(function () {
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])
        ->name('authorizations.authorize');

    // Consent screen post-back. First-party clients skip this entirely (see
    // App\Models\OAuthClient::skipsAuthorization()), but the routes must exist
    // for any client that is not on that list.
    Route::middleware('auth')->group(function () {
        Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
            ->name('authorizations.approve');

        Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])
            ->name('authorizations.deny');
    });
});
