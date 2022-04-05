<?php

use App\Http\Controllers\Livewire\GuidelinesController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SubscribeRedirectController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebhookController;

/*
|------------------
| JETSTREAM LIVEWIRE
|------------------
*/
use Laravel\Jetstream\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\TeamInvitationController;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Livewire\TeamController;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController;
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

        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::get('/pricing', [StripeController::class, 'index'])->name('pricing');

        /*
        |------------------
        | JETSTREAM LIVEWIRE
        |------------------
        */
        Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
            if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
                Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
                Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
            }

            Route::group(['middleware' => ['auth:' . config('fortify.guard'), 'verified']], function () {
                // User & Profile...
                Route::get('/user/profile', [UserProfileController::class, 'show'])
                    ->name('profile.show');

                // API...
                if (Jetstream::hasApiFeatures()) {
                    Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
                }

                // Teams...
                if (Jetstream::hasTeamFeatures()) {
                    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
                    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
                    Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

                    Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                        ->middleware(['auth'])
                        ->name('team-invitations.accept');
                    Route::get('/team-invitations/{invitation}/reject', [TeamInvitationController::class, 'destroy'])
                        ->middleware(['auth'])
                        ->name('team-invitations.reject');

                    Route::get('/teams/{team}/false-positive', [GuidelinesController::class, 'editFalsePositives'])->name('false-positive');

                    Route::get('/teams/{team}/term-replacement', [GuidelinesController::class, 'editTermReplacements'])->name('term-replacement');

                    Route::get('/teams/{team}/organization-guidelines', [GuidelinesController::class, 'editOrganizationGuidelines'])->name('organization-guidelines');
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
        | SOCIALSTREAM
        |------------------
        */
        Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
            Route::redirect('/login', '/')->name('login');
            Route::redirect('/register', '/')->name('register');
            Route::get('/logout/{provider}', [OAuthController::class, 'logout'])->name('logout');
            Route::get('/oauth/{provider}/{policy}', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
            Route::get('/oauth/{provider}/{policy}/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
        });
        /*
        |------------------
        | \SOCIALSTREAM
        |------------------
        */

        /*
        |------------------
        | CASHIER
        |------------------
        */

        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('/stripe/portal', [StripeController::class, 'portal'])->name('stripe.portal');
        });

        /*
        |------------------
        | /CASHIER
        |------------------
        */
    }
);

Route::post(
    '/stripe/webhook',
    [WebhookController::class, 'handleWebhook']
)->name('cashier.webhook');
