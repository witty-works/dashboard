<?php

namespace App\Http\Controllers;

use App\Helpers\OfficeSsoHelper;
use App\Models\ConnectedAccount as ModelsConnectedAccount;
use App\Models\User;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Http\Controllers\OAuthController as BaseOAuthController;
use JoelButcher\Socialstream\Socialstream;
use SocialiteProviders\Manager\Contracts\OAuth2\ProviderInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Socialite;
use Illuminate\Support\Facades\Session;

use Exception;

class OAuthController extends BaseOAuthController
{
    public const AZURE_AD_B2C_PROVIDER = 'azureadb2c';
    public const OFFICE_PROVIDER = 'microsoft_office';

    public function logout(string $provider)
    {
        Session::flush();

        Auth::logout();

        $provider = self::getProvider($provider);

        return redirect($provider->logout(route('login')));
    }

    public function redirectToProvider(string $provider, GeneratesProviderRedirect $generator, $policy = 'login')
    {
        $redirectUri = null;
        if ($this->isWittyWorksUrl(back()->getTargetUrl(), true)) {
            $redirectUri = back()->getTargetUrl();
        }

        $redirectUri = request()->get('redirect_uri', $redirectUri);
        if ($this->isWittyWorksUrl($redirectUri)) {
            session()->put('socialstream.previous_url', $redirectUri);
        } else {
            session()->remove('socialstream.previous_url');
        }

        return $generator->generate($provider, $policy);
    }

    public function redirectToProviderBrowserLogin(Request $request, GeneratesProviderRedirect $generator)
    {
        $redirectUri = $request->get('redirect_uri');
        $register = $request->has('register');

        $user = $request->user();
        if ($user) {
            $account = $user->currentConnectedAccount;
            if ($account && $account->token) {
                $data = [
                    'email' => strtolower($user->email),
                    'access_token' => $account->token,
                    'refresh_token' => $account->refresh_token,
                ];

                return self::returnAccessTokenResponse($data, $redirectUri);
            }
        }

        $policy = $register ? 'browser_register' : 'browser_login';
        $response = $generator->generate(self::AZURE_AD_B2C_PROVIDER, $policy);
        if ($redirectUri && $this->validateRedirectUri($redirectUri)) {
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
            $provider = self::getProvider(self::AZURE_AD_B2C_PROVIDER, 'browser_login');
            $provider->setRefreshToken($refreshToken);
            $tokens = self::getAccessTokenResponse($provider, true);
        } catch (\Exception $e) {
            # refresh token has expired?
            $connectedAccount = ModelsConnectedAccount::where('refresh_token', $refreshToken)->first();
            if ($connectedAccount) {
                $connectedAccount->token = null;
                $connectedAccount->refresh_token = null;
                $connectedAccount->save();
            }

            return response()->json(['error' => 'Not authorized.'], 403);
        }

        return response()->json($tokens);
    }


    public function handleOfficeSsoRegister(Request $request)
    {
        $token = $request->get('token');
        $officeSsoHelper = new OfficeSsoHelper();

        try {
            $claims = $officeSsoHelper->validateIdToken($token);
        } catch (Exception $e) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=failed');
        }

        $email = strtolower($claims['preferred_username'] ?? '');

        $user = Auth::user();
        if (
            $user instanceof User
            && $user->email === $email
            && $user->has_consented_to_terms_of_service
        ) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=success');
        }

        $user = Jetstream::newUserModel()->where('email', $email)->first();

        // no account for email that has consented to the terms => show the form
        if ($request->getMethod() === 'POST') {
            $validated = $request->validate([
                'has_consented_to_terms_of_service' => 'required|in:1',
                'has_consented_to_mailing' => 'nullable|boolean',
            ]);
        } else {
            $validated = $user instanceof User && $user->has_consented_to_terms_of_service;
        }

        // not yet consented => show the form again
        if (!$validated) {
            $params = [
                'name' => $claims['name'] ?? null,
                'email' => $email,
                'token' => $token,
            ];

            return view('office_register', $params);
        }

        $providerAccount = $officeSsoHelper->getProviderAccount($claims);
        $providerAccount->setToken($token);
        if (is_array($validated) && $validated['has_consented_to_terms_of_service']) {
            $providerAccount->attributes['has_consented_to_terms_of_service'] = true;
            $providerAccount->attributes['has_consented_to_mailing'] = $validated['has_consented_to_mailing'];
        }

        $this->handleProviderAccount($request, $providerAccount, self::OFFICE_PROVIDER);

        return redirect(config('services.microsoft_office.redirect_uri') . '?status=success');
    }

    public function handleOfficeSsoLogin(Request $request)
    {
        $officeSsoHelper = new OfficeSsoHelper();

        try {
            $claims = $officeSsoHelper->validateIdToken($request->get('token'));
        } catch (Exception $e) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=failed');
        }

        $email = strtolower($claims['preferred_username'] ?? '');

        $user = Auth::user();
        if ($user !== null) {
            Auth::logout();
        }

        $user = Jetstream::newUserModel()->where('email', $email)->first();

        // no account for email that has consented to the terms => show the form
        if ($user === null || !$user->has_consented_to_terms_of_service) {
            return $this->handleOfficeSsoRegister($request);
        }

        return parent::login($user);
    }

    public function handleProviderCallback(Request $request, string $provider, ResolvesSocialiteUsers $resolver, $policy = 'login')
    {
        if ($request->has('error')) {
            return Auth::check()
                ? redirect(config('fortify.home'))->dangerBanner($request->error_description)
                : redirect()->route('register')->withErrors($request->error_description);
        }

        try {
            /** @var \Laravel\Socialite\Two\User $providerAccount */
            $providerAccount = $resolver->resolve($provider, $policy);
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $tokens = self::getAccessTokenResponse(self::getProvider($provider, $policy));
        if (!empty($tokens['access_token'])) {
            $providerAccount->setToken($tokens['access_token']);
        }
        if (!empty($tokens['refresh_token'])) {
            $providerAccount->setRefreshToken($tokens['refresh_token']);
        }

        // Authenticated...
        $user = Auth::user();
        if ($user !== null) {
            $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        $user = $this->handleProviderAccount($request, $providerAccount, $provider);

        return $this->login($user);
    }

    protected function handleProviderAccount(Request $request, $providerAccount, string $provider)
    {
        $userData = [];

        if (!empty($providerAccount->attributes['has_consented_to_terms_of_service'])) {
            $userData['has_consented_to_terms_of_service'] = $providerAccount->attributes['has_consented_to_terms_of_service'];
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if (!$account) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();
            if ($user) {
                $user->switchConnectedAccount(
                    $this->createsConnectedAccounts->create($user, $provider, $providerAccount)
                );
            } else {
                $user = $this->createsUser->create($provider, $providerAccount);
                $userData['hubspotutk'] = $request->cookie('hubspotutk');
            }

            if (!empty($providerAccount->attributes['has_consented_to_mailing'])) {
                $userData['has_consented_to_mailing'] = $providerAccount->attributes['has_consented_to_mailing'];
            }
        } else {
            $user = $account->user;

            $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

            $userData['current_connected_account_id'] = $account->id;
        }

        if (!empty($userData)) {
            $user->forceFill($userData)->save();
        }

        return $user;
    }

    protected function isWittyWorksUrl($url, $strict = false)
    {
        if (empty($url)) {
            return false;
        }

        $result = parse_url($url);
        if ($strict) {
            return $result['host'] === 'dashboard.witty.works';
        }

        if (
            $result['host'] === 'witty.works'
            || str_ends_with($result['host'], '.witty.works')
        ) {
            return true;
        }

        return false;
    }

    protected function checkAllowedRedirectUri($redirectUri)
    {
        $allowedRedirectUris = config('services.azureadb2c.redirect_uri');
        if (is_array($allowedRedirectUris)) {
            foreach ($allowedRedirectUris as $uri) {
                if (strpos($redirectUri, $uri) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function validateRedirectUri($redirectUri)
    {
        return config('services.azureadb2c.validate_redirect_uri_disabled')
            || strpos($redirectUri, 'moz-extension://') === 0
            || $this->isWittyWorksUrl($redirectUri)
            || $this->checkAllowedRedirectUri($redirectUri);
    }

    protected static function getAccessTokenResponse(ProviderInterface $provider, $updateAccount = false)
    {
        $socialiteUser = $provider->user();

        $tokens = [
            'email' => User::getEmailFromProvider($socialiteUser->user),
            'refresh_token' => $socialiteUser->accessTokenResponseBody['refresh_token'] ?? null,
            'access_token' => $socialiteUser->accessTokenResponseBody['access_token'] ?? null,
        ];

        if ($updateAccount) {
            $connectedAccount = ModelsConnectedAccount::where('provider_id', $socialiteUser->id)->first();
            if ($connectedAccount) {
                $connectedAccount->token = $tokens['access_token'];
                $connectedAccount->refresh_token = $tokens['refresh_token'];
                $connectedAccount->save();
            }
        }

        return $tokens;
    }

    protected function returnAccessTokenResponse($data, $redirectUri = null)
    {
        if (empty($redirectUri)) {
            $redirectUri = request()->get('state');
        }

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
        $policy = self::isBrowserLogin();
        if ($policy) {
            $provider = self::getProvider(self::AZURE_AD_B2C_PROVIDER, $policy);
            return $this->returnAccessTokenResponse(self::getAccessTokenResponse($provider, true));
        }

        if (!$account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
        } elseif ($account->user_id === $user->id) {
            $user->updateName($providerAccount);
            $user->saveQuietly();
        }

        $redirectUri = session()->get('socialstream.previous_url');
        if ($redirectUri && $this->validateRedirectUri($redirectUri)) {
            return redirect($redirectUri);
        }

        return redirect(config('fortify.home'));
    }

    /**
     * Authenticate the given user and return a login response.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @return mixed
     */
    protected function login($user)
    {
        $loginResponse = parent::login($user);

        $policy = self::isBrowserLogin();
        if ($policy) {
            $provider = self::getProvider(self::AZURE_AD_B2C_PROVIDER, $policy);
            $tokens = self::getAccessTokenResponse($provider, true);

            return $this->returnAccessTokenResponse($tokens);
        }

        if ($user->wasRecentlyCreated) {
            return redirect()->route('download');
        }

        $redirectUri = session()->get('socialstream.previous_url');
        if ($redirectUri) {
            session()->remove('socialstream.previous_url');

            return redirect($redirectUri);
        }

        return $loginResponse;
    }

    public static function isBrowserLogin($policy = null)
    {
        $browserLoginPolicies = ['browser_login', 'browser_register'];

        $request = request();
        foreach ($browserLoginPolicies as $browserLoginPolicy) {
            $url = route('oauth.callback', ['provider' => 'azureadb2c', 'policy' => $browserLoginPolicy]);
            if ($request->url() === $url) {
                $policy = $browserLoginPolicy;
                break;
            }
        }

        return in_array($policy, $browserLoginPolicies) ? $policy : false;
    }

    public static function getProvider($provider, $policy = null)
    {
        try {
            $provider = Socialite::driver($provider);
        } catch (InvalidArgumentException $e) {
            abort(400);
        }

        if (self::isBrowserLogin($policy)) {
            $provider->setScopes(config('services.azureadb2c.scope'));
            $provider->stateless();
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
