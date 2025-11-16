<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\UserGuidelinesApiController;
use App\Http\Controllers\OAuthController;
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
    Route::group([
        'excluded_middleware' => ['ensureStateful'],
    ], function () {
        Route::post('/refresh-token', [OAuthController::class, 'accessTokenFromRefreshToken'])
            ->name('browser.refresh_token');
    });

    Route::group([
        'prefix' => '/user',
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
