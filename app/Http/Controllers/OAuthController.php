<?php

namespace App\Http\Controllers;

use App\Helpers\OfficeSsoHelper;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\Http\Controllers\OAuthController as BaseOAuthController;
use JoelButcher\Socialstream\Socialstream;
use Mail;
use Exception;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse;
use JoelButcher\Socialstream\Contracts\SocialstreamResponse;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;

class OAuthController extends BaseOAuthController
{
    public const OFFICE_PROVIDER = 'microsoft_office';
    public const LOGIN_SOURCE = 'login_source';

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



    public function handleOfficeSsoRegister(Request $request)
    {
        $token = $request->get('token');
        $officeSsoHelper = new OfficeSsoHelper();

        $ssoDebugEmail = env('OFFICE_SSO_DEBUG_EMAIL', false);

        try {
            $claims = $officeSsoHelper->validateIdToken($token);
        } catch (Exception $e) {
            if ($ssoDebugEmail) {
                Mail::raw('token: ' . $token . "\n\n" . $e->getMessage(), function ($m) use ($ssoDebugEmail) {
                    $m->to($ssoDebugEmail)->subject('SSO Login Failed');
                });
            }

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

        $user = Socialstream::newUserModel()->where('email', $email)->first();

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

        $user = Socialstream::newUserModel()->where('email', $email)->first();

        // no account for email that has consented to the terms => show the form
        if ($user === null || !$user->has_consented_to_terms_of_service) {
            return $this->handleOfficeSsoRegister($request);
        }

        return $this->loginUser($user, self::OFFICE_PROVIDER);
    }


    protected function handleProviderAccount(Request $request, $providerAccount, string $provider)
    {
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
                $userData['hubspotutk'] = $request->cookie('hubspotutk');
            }

            if (!empty($providerAccount->attributes['has_consented_to_mailing'])) {
                $userData['has_consented_to_mailing'] = $providerAccount->attributes['has_consented_to_mailing'];
            }
        } else {
            $user = $account->user;

            $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);
        }

        if ($user->source === null) {
            $user->source = $userData['source'] = $provider;
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
        if (empty($result['host'])) {
            return false;
        }

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

    protected function loginUser(Authenticatable $user, $provider): SocialstreamResponse
    {
        Auth::login($user, Socialstream::hasRememberSessionFeatures());
        request()->session()->put(self::LOGIN_SOURCE, $provider);

        return app(OAuthLoginResponse::class);
    }


    // mockLogin feature removed

    /**
     * Issue a JWT for the browser extension after user is authenticated via username/password.
     */
    public function browserLogin(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = Auth::user();
        $key = config('app.key');
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour expiry
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
        return response()->json([
            'token' => $jwt,
            'email' => $user->email,
            'name' => $user->name,
            'id' => $user->id,
        ]);
    }
}
