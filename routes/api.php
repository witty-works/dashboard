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

Route::get('/browser-login', [OAuthController::class, 'redirectToProviderBrowserLogin'])->name('api.browser_login');
Route::post('/refresh-token', [OAuthController::class, 'accessTokenFromRefreshToken'])->name('api.refresh_token');
Route::get('/oauth/{provider}/{policy}/callback', [OAuthController::class, 'handleProviderCallback'])->name('api.callback');

Route::delete('/user/language/domains', [UserGuidelinesApiController::class, 'deleteDomain'])->name('api.domains.delete');
Route::put('/user/language/domains', [UserGuidelinesApiController::class, 'putDomain'])->name('api.domains.put');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/analytics', [AnalyticsController::class, 'userApi'])->name('api.user_analytics');
    Route::get('/team/analytics', [AnalyticsController::class, 'organizationApi'])->name('api.team_analytics');
});
