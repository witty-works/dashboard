<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\UserGuidelinesApiController;
use App\Http\Controllers\UserInfoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (config('app.browsers')) {
    /*
     * Token issuing and refreshing both live on POST /oauth/token now
     * (authorization_code + PKCE, and the refresh_token grant) — see
     * routes/oauth.php. The old POST /api/refresh-token is gone and is not
     * coming back; it was an Azure AD B2C artefact whose controller method was
     * deleted in d4e2f683, leaving a public route that only ever 500'd.
     *
     * `auth:extension` accepts a Passport access token (browser extension) or a
     * Microsoft id_token (Office add-in) — see App\Auth\ExtensionUserResolver.
     * `ensureStateful` is excluded because these are token-authenticated
     * cross-origin calls, not first-party stateful SPA requests.
     *
     * docs/oauth.md has the full flow.
     */
    Route::group([
        'middleware' => ['auth:extension'],
        'excluded_middleware' => ['ensureStateful'],
    ], function () {
        // Who the bearer token belongs to. The extension needs the email to
        // label its UI; Passport access tokens carry no such claim.
        Route::get('/userinfo', UserInfoController::class)->name('api_userinfo');
    });

    Route::group([
        'prefix' => '/user',
        'middleware' => ['auth:extension'],
        'excluded_middleware' => ['ensureStateful'],
    ], function () {
        Route::delete('/language/domains', [UserGuidelinesApiController::class, 'deleteDomain'])
            ->name('user.domains.delete');
        Route::put('/language/domains', [UserGuidelinesApiController::class, 'putDomain'])
            ->name('user.domains.put');

        Route::delete('/language/ignore-words', [UserGuidelinesApiController::class, 'deleteFalsePositive'])
            ->name('user.ignored-words.delete');
        Route::put('/language/ignore-words', [UserGuidelinesApiController::class, 'putFalsePositive'])
            ->name('user.ignored-words.put');

        Route::put('/language/customize-witty', [UserGuidelinesApiController::class, 'adjustDDDLevel'])
            ->name('user.adjust_ddd_level.put');
    });
}

if (config('posthog.enabled')) {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user/analytics', [AnalyticsController::class, 'userApi'])
            ->name('api_user_analytics');
        Route::get('/team/analytics', [AnalyticsController::class, 'organizationApi'])
            ->name('api_team_analytics');
    });
}
