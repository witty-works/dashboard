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
use App\Http\Controllers\WriteController;
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
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::localize(
    function () {
        Route::impersonate();

        Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'show'])->name('terms.show');
        Route::get('/policy', [\App\Http\Controllers\PolicyController::class, 'show'])->name('policy.show');

        // The add-in opens these with its token in the URL fragment; the page
        // posts it. See OAuthController::showOfficeSsoBridge().
        Route::get('/office-login', [OAuthController::class, 'showOfficeSsoBridge'])->name('office_login');
        Route::post('/office-login', [OAuthController::class, 'handleOfficeSsoLogin'])->name('office_login_post');
        Route::get('/office-register', [OAuthController::class, 'showOfficeSsoBridge'])->name('office_register');
        Route::post('/office-register', [OAuthController::class, 'handleOfficeSsoRegister'])->name('office_register_post');

        /*
        |------------------
        | JETSTREAM LIVEWIRE
        |------------------
        */
        Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
            Route::group(['middleware' => ['auth:' . config('fortify.guard'), 'team']], function () {
                Route::get('/', [WelcomeController::class, 'show'])->name('root');

                // Invite only: where a user without a team waits.
                Route::view('/no-team', 'no-team')->name('no-team');


                Route::get('/write', [WriteController::class, 'show'])
                    ->middleware('verified')
                    ->name('write');
                // Witty GPT's old address.
                Route::get('/prompt', fn () => redirect()->route('write'))->name('prompt');

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
// The write page's OAuth callback: outside the locale prefix, so the redirect
// URI registered for its client (passport:editor-client) stays fixed.
Route::get('/write/callback', [WriteController::class, 'show'])
    ->middleware(['web', 'auth:' . config('fortify.guard'), 'verified'])
    ->name('write.callback');

// The emailed link for deleting an account without a password: outside the
// locale prefix, so the signature covers the address the user opens. The
// signed link proves the mailbox, so it works without a session: such
// accounts sign in only from Word, and the mail opens in another browser.
Route::get('/account/delete/{user}', [\App\Http\Controllers\DeleteAccountController::class, 'show'])
    ->middleware(['web', 'signed'])
    ->name('account.delete.confirm');
Route::post('/account/delete/{user}', [\App\Http\Controllers\DeleteAccountController::class, 'destroy'])
    ->middleware(['web', 'signed'])
    ->name('account.delete.destroy');

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
    // Entry point for the extension's "Sign in" / "Sign up" buttons: a thin
    // redirect into the PKCE flow in routes/oauth.php. It no longer issues a
    // token of its own.
    Route::get('/browser-login', [OAuthController::class, 'browserLogin'])->name('browser_login');
}

// The superadmins' pages, outside the locale prefix.
Route::middleware(['auth', 'can:' . config('admin.permission')])
    ->prefix(config('admin.prefix'))
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/' . config('admin.prefix') . '/teams')->name('home');
        Route::view('/teams', 'admin.teams')->name('teams');
        Route::view('/users', 'admin.users')->name('users');
    });
