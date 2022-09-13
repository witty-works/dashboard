<?php

namespace App\Http\Controllers;

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
use SocialiteProviders\Manager\Config;

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
        $response = $generator->generate($this->provider, 'browser_login');

        $redirectUri = $request->get('redirect_uri');
        if ($this->validateRedirectUri($redirectUri)) {
            $targetUrl = $response->getTargetUrl();
            $url = parse_url($targetUrl);
            if (!empty($url['query'])) {
                $result = null;
                parse_str($url['query'], $result);
                $result['state'] = $redirectUri;

                $newTargetUrl = $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . http_build_query($result);

                $response->setTargetUrl($newTargetUrl);
            }
        }

        return $response;
    }

    public function accessTokenFromRefreshToken(Request $request)
    {
        $refreshToken = $request->json('token');
        if (!$refreshToken) {
            return response()->json(['error' => "'token' parameter empty"], 400);
        }

        try {
            $provider = self::getProvider($this->provider, 'browser_login');
            $provider->setRefreshToken($refreshToken);

            return response()
                ->json($this->getAccessTokenResponse($provider));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }
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
        if (
            FortifyFeatures::enabled(FortifyFeatures::registration())
            && ($request->is('api/*') || session()->get('socialstream.previous_url') === route('register'))
            && !$account
        ) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->handleUserAlreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($account, $provider, $providerAccount);
        }

        if (!Features::hasCreateAccountOnFirstLoginFeatures() && !$account) {
            return redirect()->route('login')->withErrors(
                __('content.account_not_found')
            );
        }

        $userData = [];

        if (!empty($providerAccount->user['extension_termsOfUseConsentDateTime'])) {
            $userData['has_consented_to_terms_of_service'] = $providerAccount->user['extension_termsOfUseConsentDateTime'];
        }

        if (!empty($providerAccount->user['extension_MailingConsented'])) {
            $userData['has_consented_to_mailing'] = $providerAccount->user['extension_MailingConsented'] === 'Yes';
        }

        if (!$account) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();
            if ($user) {
                $user->switchConnectedAccount(
                    $this->createsConnectedAccounts->create($user, $provider, $providerAccount)
                );
            } else {
                $user = $this->createsUser->create($provider, $providerAccount);
            }
        } else {
            $user = $account->user;

            $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

            $userData['current_connected_account_id'] = $account->id;
        }

        if (!empty($userData)) {
            $user->forceFill($userData)->save();
        }

        return $this->login($user);
    }

    protected function validateRedirectUri($redirectUri)
    {
        return config('services.azureadb2c.validate_redirect_uri_disabled')
            || strpos($redirectUri, 'moz-extension://') === 0
            || in_array($redirectUri, config('services.azureadb2c.redirect_uri'));
    }

    protected function getAccessTokenResponse(ProviderInterface $provider)
    {
        $socialiteUser = $provider->user();

        return [
            'email' => User::getEmailFromProvider($socialiteUser->user),
            'refresh_token' => $socialiteUser->accessTokenResponseBody['refresh_token'] ?? null,
            'access_token' => $socialiteUser->accessTokenResponseBody['access_token'] ?? null,
        ];
    }

    protected function returnAccessTokenResponse(ProviderInterface $provider)
    {
        $data = $this->getAccessTokenResponse($provider);

        $redirectUri = request()->get('state');
        if ($redirectUri && $this->validateRedirectUri($redirectUri)) {
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
        if (self::isBrowserLogin()) {
            return $this->returnAccessTokenResponse(self::getProvider($this->provider, 'browser_login'));
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
        if (self::isBrowserLogin()) {
            return $this->returnAccessTokenResponse(self::getProvider($this->provider, $policy));
        }

        return $loginResponse;
    }

    static public function isBrowserLogin($policy = null)
    {
        $browserLoginPolicies = ['browser_login'];
        $request = request();
        foreach ($browserLoginPolicies as $browserLoginPolicy) {
            $url = route('browser.callback', ['provider' => 'azureadb2c', 'policy' => $browserLoginPolicy]);
            if ($request->url() === $url) {
                $policy = $browserLoginPolicy;
                break;
            }
        }

        return in_array($policy, $browserLoginPolicies);
    }

    static public function getProvider($provider, $policy = null)
    {
        $provider = Socialite::driver($provider);

        if (OAuthController::isBrowserLogin($policy)) {
            $provider->setScopes(config('services.azureadb2c.scope'));
            $provider->stateless();
            $config = new Config(
                config('services.azureadb2c.client_id'),
                config('services.azureadb2c.client_secret'),
                config('services.azureadb2c.redirect'),
                [
                    'domain' => config('services.azureadb2c.domain'),
                    'policy' => config('services.azureadb2c.policy'),
                    'redirect_template' => config('services.azureadb2c.api_redirect_template'),
                ],
            );
            $provider->setConfig($config);
        }

        return $provider;
    }

    public function mockLogin(Request $request)
    {
        if (!config('app.mock_login')) {
            abort(404);
        }

        $user = User::where('email', $request->get('email'))->firstOrFail();

        return $this->login($user);
    }
}
