<?php

namespace JoelButcher\Socialstream\Actions;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Socialite\Contracts\User as ProviderUser;
use App\Http\Controllers\OAuthController;

class AuthenticateOAuthCallback implements AuthenticatesOAuthCallback
{
    use InteractsWithComposer;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected Guard $guard,
        protected CreatesUserFromProvider $createsUser,
        protected CreatesConnectedAccounts $createsConnectedAccounts,
        protected UpdatesConnectedAccounts $updatesConnectedAccounts
    ) {
        //
    }

    public function authenticate(string $provider, ProviderUser $providerAccount, $policy = 'login'): Response|RedirectResponse|LoginResponse
    {
        $request = request();

        $tokens = OAuthController::getAccessTokenResponse(OAuthController::getProvider($provider, $policy));
        if (!empty($tokens['access_token'])) {
            $providerAccount->setToken($tokens['access_token']);
        }
        if (!empty($tokens['refresh_token'])) {
            $providerAccount->setRefreshToken($tokens['refresh_token']);
        }

        // Authenticated...
        $user = auth()->user();
        if ($user !== null) {
            $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        $user = $this->handleProviderAccount($providerAccount, $provider);

        $request->session()->put(OAuthController::LOGIN_SOURCE, OAuthController::AZURE_AD_B2C_PROVIDER);

        return $this->login($user);
    }


    protected function handleProviderAccount($providerAccount, string $provider)
    {
        $request = request();
        $userData = [];

        if (!empty($providerAccount->attributes['has_consented_to_terms_of_service'])) {
            $userData['has_consented_to_terms_of_service'] = $providerAccount->attributes['has_consented_to_terms_of_service'];
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if (!$account) {
            $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();
            if ($user) {
                $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
            } else {
                $user = $this->createsUser->create($provider, $providerAccount);
            }

            $userData['hubspotutk'] = $request->cookie('hubspotutk', $user->hubspotutk);
            if (!empty($providerAccount->attributes['has_consented_to_mailing'])) {
                $userData['has_consented_to_mailing'] = $providerAccount->attributes['has_consented_to_mailing'];
            }
        } else {
            $user = $account->user;

            $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);
        }

        if (!empty($userData)) {
            $user->forceFill($userData)->save();
        }

        return $user;
    }

    /**
     * Handle connection of accounts for an already authenticated user.
     */
    protected function alreadyAuthenticated(Authenticatable $user, ?ConnectedAccount $account, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        $policy = OAuthController::isBrowserLogin();
        if ($policy) {
            $provider = OAuthController::getProvider(OAuthController::AZURE_AD_B2C_PROVIDER, $policy);
            return OAuthController::returnAccessTokenResponse(OAuthController::getAccessTokenResponse($provider, true));
        }

        // Get the route
        $route = match (true) {
            Route::has('profile.show') => route('profile.show'),
            Route::has('dashboard') => route('dashboard'),
            Route::has('home') => route('home'),
            default => RouteServiceProvider::HOME
        };

        if (!$account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect()->to($route);
        }

        if ($account->user_id === $user->id) {
            $user->updateName($providerAccount);
            $user->saveQuietly();
        }

        $redirectUri = session()->get('socialstream.previous_url');
        if ($redirectUri && OAuthController::validateRedirectUri($redirectUri)) {
            return redirect($redirectUri);
        }

        return redirect()->to($route);
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Authenticatable $user): RedirectResponse|LoginResponse
    {
        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        // Because users can have multiple stacks installed for which they may wish to use
        // Socialstream for, we will need to determine the redirect path based on a few
        // different factors, such as the presence of Filament's auth routes etc.

        $previousUrl = session()->pull('socialstream.previous_url');

        return match (true) {
            $this->hasComposerPackage('laravel/jetstream') => app(LoginResponse::class),
            default => redirect()
                ->to(RouteServiceProvider::HOME),
        };
    }
}
