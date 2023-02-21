<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Livewire\OrganizationGuidelinesController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebhookController;

/*
|------------------
| JETSTREAM LIVEWIRE
|------------------
*/
use App\Http\Controllers\TeamInvitationController;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use App\Http\Controllers\Livewire\TeamController;
use App\Http\Controllers\Livewire\UserGuidelinesController;
use App\Http\Controllers\UserProfileController;
use Laravel\Jetstream\Jetstream;
/*
|------------------
| \JETSTREAM LIVEWIRE
|------------------
*/

/*
|------------------
| SOCIALSTREAM
|------------------
*/
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\WelcomeController;
/*
|------------------
| \SOCIALSTREAM
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

        /*
        |------------------
        | JETSTREAM LIVEWIRE
        |------------------
        */
        Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
            Route::group(['middleware' => ['auth:' . config('fortify.guard')]], function () {
                Route::get('/', [WelcomeController::class, 'show'])->name('root');

                Route::get('/download', function () {
                    return redirect(config('app.download_url'));
                })->name('download');

                Route::get('/editor', function () {
                    return view('editor');
                })->name('editor');

                Route::get('/subscribe', [StripeController::class, 'subscribe'])->name('stripe.subscribe');

                Route::get('/user/onboarding', [UserProfileController::class, 'onboarding'])
                    ->name('profile.onboarding');

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
                    Route::get('/team/create', [TeamController::class, 'create'])->name('teams.create');
                    Route::get('/team/show', [TeamController::class, 'show'])->name('teams.show');
                    Route::get('/team/subscription', [StripeController::class, 'show'])->name('teams.subscription');

                    Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                        ->middleware(['auth'])
                        ->name('team-invitations.accept');
                    Route::get('/team-invitations/{invitation}/reject', [TeamInvitationController::class, 'destroy'])
                        ->middleware(['auth'])
                        ->name('team-invitations.reject');

                    Route::redirect('/user/language', '/user/language/language-settings')->name('user.language-guidelines');
                    Route::get('/user/language/language-settings', [UserGuidelinesController::class, 'customizeWitty'])->name('user.language-settings');
                    Route::get('/user/language/language-settings/reset', [UserGuidelinesController::class, 'reset'])->name('user.language-settings-reset');
                    Route::get('/user/language/dictionary', [UserGuidelinesController::class, 'termReplacements'])->name('user.dictionary');
                    Route::get('/user/language/ignore-words', [UserGuidelinesController::class, 'falsePositives'])->name('user.ignored-words');
                    Route::get('/user/language/privacy-settings', [UserGuidelinesController::class, 'domains'])->name('user.privacy-settings');
                    Route::get('/user/analytics', [AnalyticsController::class, 'user'])->name('user_analytics');

                    Route::redirect('/team/language', '/team/language/language-settings')->name('teams.language-guidelines');
                    Route::get('/team/language/language-settings', [OrganizationGuidelinesController::class, 'customizeWitty'])->name('teams.language-settings');
                    Route::get('/team/language/dictionary', [OrganizationGuidelinesController::class, 'termReplacements'])->name('teams.dictionary');
                    Route::get('/team/language/ignored-words', [OrganizationGuidelinesController::class, 'falsePositives'])->name('teams.ignored-words');
                    Route::get('/team/language/privacy-settings', [OrganizationGuidelinesController::class, 'domains'])->name('teams.privacy-settings');
                    Route::get('/team/analytics', [AnalyticsController::class, 'organization'])->name('team_analytics');
                }
            });
        });
        /*
        |------------------
        | \JETSTREAM LIVEWIRE
        |------------------
        */

        /*
        |------------------
        | CASHIER
        |------------------
        */

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/stripe/portal', [StripeController::class, 'portal'])->name('stripe.portal');
        });

        /*
        |------------------
        | /CASHIER
        |------------------
        */
    }
);


/*
|------------------
| SOCIALSTREAM
|------------------
*/
Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
    Route::get('/', [WelcomeController::class, 'show'])->name('login');
    Route::redirect('/register', '/')->name('register');
    Route::get('/mock-login', [OAuthController::class, 'mockLogin'])->name('mock-login');
    Route::get('/logout/{provider}', [OAuthController::class, 'logout'])->name('logout');
    Route::get('/oauth/{provider}/{policy}', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
    Route::get('/oauth/{provider}/{policy}/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
});

/*
|------------------
| \SOCIALSTREAM
|------------------
*/

Route::post(
    '/stripe/webhook',
    [WebhookController::class, 'handleWebhook']
)->name('cashier.webhook');


Route::fallback(function () {
    return view('errors.404');
});
