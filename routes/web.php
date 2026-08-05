<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Livewire\OrganizationGuidelinesController;
use Illuminate\Support\Facades\Route;


/*
|------------------
| JETSTREAM LIVEWIRE
|------------------
*/
use App\Http\Controllers\TeamInvitationController;
use Laravel\Jetstream\Http\Controllers\CurrentTeamController;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use App\Http\Controllers\Livewire\TeamController;
use App\Http\Controllers\Livewire\PromptController;
use App\Http\Controllers\Livewire\UserGuidelinesController;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController;
use Laravel\Jetstream\Jetstream;
/*
|------------------
| \JETSTREAM LIVEWIRE
|------------------
*/

/*
|------------------
| SOCIAL AUTH
|------------------
*/
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\WelcomeController;
/*
|------------------
| \SOCIAL AUTH
|------------------
*/

/*
|------------------
| MCAMARA LARAVELLOCALIZATION
|------------------
*/

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|------------------
| \MCAMARA LARAVELLOCALIZATION
|------------------
*/

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localize', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {
        Route::impersonate();

        Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'show'])->name('terms.show');
        Route::get('/policy', [\App\Http\Controllers\PolicyController::class, 'show'])->name('policy.show');

        Route::get('/office-login', [OAuthController::class, 'handleOfficeSsoLogin'])->name('office_login');
        Route::get('/office-register', [OAuthController::class, 'handleOfficeSsoRegister'])->name('office_register');
        Route::post('/office-register', [OAuthController::class, 'handleOfficeSsoRegister'])->name('office_register_post');

        /*
        |------------------
        | JETSTREAM LIVEWIRE
        |------------------
        */
        Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
            Route::group(['middleware' => ['auth:' . config('fortify.guard')]], function () {
                Route::get('/', [WelcomeController::class, 'show'])->name('root');


                if (config('app.llm_enabled')) {
                    Route::get('/prompt', [PromptController::class, 'show'])->name('prompt');
                }

                Route::post('/user/onboarding', [UserProfileController::class, 'storeOnboarding'])
                    ->name('profile.onboarding.store');

                // User & Profile...
                Route::get('/user/profile', [UserProfileController::class, 'show'])
                    ->name('profile.show');

                Route::get('/user/mailing', [WelcomeController::class, 'mailingConsent'])
                    ->name('user.mailing_consent');

                Route::get('/user/academy', function () {
                    return view('academy');
                })->name('academy');

                // API...
                if (Jetstream::hasApiFeatures()) {
                    Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
                }

                // Teams...
                if (Jetstream::hasTeamFeatures()) {
                    Route::get('/team/show', [TeamController::class, 'show'])->name('teams.show');
                    Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
                    Route::get('/team-invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])
                        ->middleware(['auth'])
                        ->name('team-invitations.accept');

                    Route::get('/team-invitations/{invitation}/reject', [TeamInvitationController::class, 'destroy'])
                        ->middleware(['auth'])
                        ->name('team-invitations.reject');

                    Route::redirect('/user/language', '/user/language/customize-witty')->name('user.language-guidelines');
                    Route::get('/user/language/customize-witty', [UserGuidelinesController::class, 'categorySettings'])->name('user.category-settings');
                    Route::get('/user/language/language-settings', [UserGuidelinesController::class, 'languageSettings'])->name('user.language-settings');
                    Route::get('/user/language/language-settings/reset', [UserGuidelinesController::class, 'resetLanguageSettings'])->name('user.language-settings-reset');
                    Route::get('/user/language/dictionary', [UserGuidelinesController::class, 'termReplacements'])->name('user.dictionary');
                    Route::get('/user/language/ignore-words', [UserGuidelinesController::class, 'falsePositives'])->name('user.ignored-words');
                    Route::get('/user/language/privacy-settings', [UserGuidelinesController::class, 'domains'])->name('user.privacy-settings');
                    if (config('posthog.enabled')) {
                        Route::get('/user/analytics', [AnalyticsController::class, 'user'])->name('user.analytics');
                    }

                    Route::redirect('/team/language', '/team/language/customize-witty')->name('teams.language-guidelines');
                    Route::get('/team/language/customize-witty', [OrganizationGuidelinesController::class, 'categorySettings'])->name('teams.category-settings');
                    Route::get('/team/language/language-settings', [OrganizationGuidelinesController::class, 'languageSettings'])->name('teams.language-settings');
                    Route::get('/team/language/dictionary', [OrganizationGuidelinesController::class, 'termReplacements'])->name('teams.dictionary');
                    Route::get('/team/language/ignored-words', [OrganizationGuidelinesController::class, 'falsePositives'])->name('teams.ignored-words');
                    Route::get('/team/language/privacy-settings', [OrganizationGuidelinesController::class, 'domains'])->name('teams.privacy-settings');
                    if (config('posthog.enabled')) {
                        Route::get('/team/analytics', [AnalyticsController::class, 'organization'])->name('teams.analytics');
                    }
                }
            });
        });
        /*
        |------------------
        | \JETSTREAM LIVEWIRE
        |------------------
        */
    }
);


/*
|------------------
| SOCIAL AUTH
|------------------
*/
Route::group(['middleware' => config('social_auth.middleware', ['web'])], function () {

    Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'acceptSigned'])
        ->middleware(['signed'])
        ->name('team-invitations.accept-signed');

    if (!empty(config('app.browsers'))) {
        Route::get('/download', function () {
            return view('download');
        })->name('download');
    }

    Route::get('/word-addin', function () {
        return view('word-addin');
    })->name('word-addin');
});

/*
|------------------
| \SOCIAL AUTH
|------------------
*/


Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

if (config('app.browsers')) {
    Route::get('/browser-login', [OAuthController::class, 'browserLogin'])->name('browser_login');
}
