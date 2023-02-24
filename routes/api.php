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

Route::get('/browser-login', [OAuthController::class, 'redirectToProviderBrowserLogin'])->name('browser_login');
Route::post('/refresh-token', [OAuthController::class, 'accessTokenFromRefreshToken'])->name('browser.refresh_token');
Route::get('/oauth/{provider}/{policy}/callback', [OAuthController::class, 'handleProviderCallback'])->name('browser.callback');

Route::group([
    'prefix' => '/user/language',
    'excluded_middleware' => ['ensureStateful'],
], function () {
    Route::delete('/domains', [UserGuidelinesApiController::class, 'deleteDomain'])->name('user.domains.delete');
    Route::put('/domains', [UserGuidelinesApiController::class, 'putDomain'])->name('user.domains.put');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/analytics', [AnalyticsController::class, 'userApi'])->name('api_user_analytics');
    Route::get('/team/analytics', [AnalyticsController::class, 'organizationApi'])->name('api_team_analytics');
});
