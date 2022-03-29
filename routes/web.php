<?php

use App\Http\Controllers\Livewire\GuidelinesController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SubscribeRedirectController;

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
        | SPARK
        |------------------
        */
        Route::get('/spark-redirect', [SubscribeRedirectController::class, 'redirect'])->name('spark.redirect');

        if (config('spark.enabled')) {
            Route::group([
                'namespace' => 'Spark\Http\Controllers',
                'prefix' => 'spark'
            ], function () {
                Route::group(['middleware' => config('spark.middleware', ['web', 'auth'])], function () {
                    // Subscription...
                    Route::post('/subscription', 'NewSubscriptionController');
                    Route::put('/subscription', 'UpdateSubscriptionController');
                    Route::put('/subscription/cancel', 'CancelSubscriptionController');
                    Route::put('/subscription/resume', 'ResumeSubscriptionController');

                    // Payment Method...
                    Route::put('/subscription/payment-method', 'UpdatePaymentMethodController');

                    // Billing Information...
                    Route::put('/billing-information', 'UpdateBillingInformationController');

                    // Receipt Emails...
                    Route::put('/receipt-emails', 'UpdateReceiptEmailsController');

                    // Apply a Coupon...
                    Route::put('/coupon', 'ApplyCouponController');

                    // Stripe Setup Intent Tokens...
                    Route::get('/token', 'StripeTokenController');

                    // Vat Rate Controller...
                    Route::post('/tax-rate', 'TaxController');

                    // Billing Information...
                    Route::get('/{type}/{id}/receipts/{receiptId}/download', 'DownloadReceiptController')->name('receipts.download');
                });
            });

            Route::group([
                'middleware' => config('spark.middleware', ['web', 'auth']),
                'namespace' => 'Spark\Http\Controllers',
                'prefix' => config('spark.path'),
            ], function () {
                Route::get('/{type?}/{id?}', 'BillingPortalController')->name('spark.portal');
            });
        }

        /*
        |------------------
        | /SPARK
        |------------------
        */
    }
);

/*
|------------------
| SPARK
|------------------
*/

Route::group([
    'namespace' => 'Spark\Http\Controllers',
    'prefix' => 'spark'
], function () {
    // Stripe Webhook Controller...
    Route::post('webhook', 'WebhookController@handleWebhook')->name('spark.webhook');
});

/*
|------------------
| /SPARK
|------------------
*/
