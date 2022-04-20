<?php

namespace App\Http\Controllers;

use App\Actions\Socialstream\ResolveSocialiteUser;
use App\Models\User;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Http\Controllers\OAuthController as BaseOAuthController;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Features;
use SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Socialite;

class OAuthController extends BaseOAuthController
{
    protected $provider = 'azureadb2c';

    public function logout(string $provider, GeneratesProviderRedirect $generator, $policy = 'login')
    {
        return $generator->logout($provider, $policy);
    }

    public function redirectToProvider(Request $request, string $provider, GeneratesProviderRedirect $generator, $policy = 'login')
    {
        session()->put('socialstream.previous_url', back()->getTargetUrl());

        return $generator->generate($provider, $policy);
    }

    public function redirectToProviderBrowserLogin(Request $request, GeneratesProviderRedirect $generator)
    {
        $redirectUri = $request->get('redirect_uri');
        if ($this->validateRedirectUri($redirectUri)) {
            session()->put('socialstream.browser_login_redirect_uri', $redirectUri);
        } else {
            session()->remove('socialstream.browser_login_redirect_uri');
        }

        return $this->redirectToProvider($request, $this->provider, $generator, 'browser_login');
    }

    public function accessTokenFromRefreshToken(Request $request)
    {
        $refreshToken = $request->get('token');
        if (!$refreshToken) {
            abort(400, "'refresh_token` parameter empty");
        }

        $provider = Socialite::driver($this->provider);
        $provider->setRefreshToken($refreshToken);
        $provider->setScopes(config('services.azureadb2c.scope'));

        return response()
            ->json($this->getAccessTokenResponse($provider));
    }

    public function handleProviderCallback(Request $request, string $provider, ResolvesSocialiteUsers $resolver, $policy = 'login')
    {
        if ($request->has('error')) {
            return Auth::check()
                ? redirect(config('fortify.home'))->dangerBanner($request->error_description)
                : redirect()->route('register')->withErrors($request->error_description);
        }

        try {
            $providerAccount = $resolver->resolve($provider, $policy);
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (!is_null($user = Auth::user())) {
            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        // Registration...
        if (FortifyFeatures::enabled(FortifyFeatures::registration()) && session()->get('socialstream.previous_url') === route('register') && !$account) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->handleUserAlreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($account, $provider, $providerAccount);
        }

        if (!Features::hasCreateAccountOnFirstLoginFeatures() && !$account) {
            return redirect()->route('login')->withErrors(
                __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => $provider])
            );
        }

        if (Features::hasCreateAccountOnFirstLoginFeatures() && !$account) {
            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                return redirect()->route('login')->withErrors(
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            return $this->login($user);
        }

        $user = $account->user;

        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $user->forceFill([
            'current_connected_account_id' => $account->id,
        ])->save();

        return $this->login($user);
    }

    protected function validateRedirectUri($redirectUri)
    {
        return in_array($redirectUri, config('services.azureadb2c.redirect_uri'));
    }

    protected function getAccessTokenResponse(ProviderInterface $provider)
    {
        $socialiteUser = $provider->user();

        return [
            'email' => $socialiteUser->user['emails'][0],
            'refresh_token' => $socialiteUser->accessTokenResponseBody['refresh_token'] ?? null,
            'access_token' => $socialiteUser->accessTokenResponseBody['access_token'] ?? null,
        ];
    }

    protected function returnAccessTokenResponse(ProviderInterface $provider)
    {
        $data = $this->getAccessTokenResponse($provider);

        $redirectUri = session()->get('socialstream.browser_login_redirect_uri');
        if ($this->validateRedirectUri($redirectUri)) {
            $redirectUri .= '?' . http_build_query($data);

            return redirect($redirectUri);
        }

        return view('browser-login', $data);
    }

    /**
     * 
     * @param User $user 
     * @param ConnectedAccount $account 
     * @param string $provider 
     * @param AbstractUser $providerAccount 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws RouteNotFoundException 
     */
    protected function alreadyAuthenticated($user, $account, $provider, $providerAccount)
    {
        if (ResolveSocialiteUser::isBrowserLogin()) {
            return $this->returnAccessTokenResponse(Socialite::driver($provider));
        }

        $route = route('profile.show');

        if ($account && $account->user_id !== $user->id) {
            return redirect($route);
        }

        if (!$account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect($route);
        }

        $user->updateName($providerAccount);
        $user->save();

        return redirect($route);
    }

    /**
     * Authenticate the given user and return a login response.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @return mixed
     */
    protected function login($user, $policy = 'login')
    {
        $loginResponse = parent::login($user);
        if (ResolveSocialiteUser::isBrowserLogin()) {
            return $this->returnAccessTokenResponse(Socialite::driver($this->provider));
        }

        return $loginResponse;
    }
}
