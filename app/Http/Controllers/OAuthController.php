<?php

namespace App\Http\Controllers;

use App\Contracts\SocialAuth\CreatesConnectedAccounts;
use App\Contracts\SocialAuth\CreatesUserFromProvider;
use App\Contracts\SocialAuth\UpdatesConnectedAccounts;
use App\Helpers\OfficeSsoHelper;
use App\Models\ConnectedAccount;
use App\Models\User;
use App\Support\InviteOnly;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Throwable;

/**
 * Handles Microsoft Office SSO.
 *
 * This used to extend JoelButcher\Socialstream\Http\Controllers\OAuthController,
 * but that package was archived upstream in December 2025 and never supported
 * Laravel 13. Nothing here ever called into the parent — the inherited
 * redirect/callback/prompt/confirm actions only served the generic OAuth routes,
 * and socialstream.providers has always been empty — so the base class was
 * dropped and the handful of Socialstream:: helpers inlined.
 */
class OAuthController extends Controller
{
    public const OFFICE_PROVIDER = 'microsoft_office';
    public const LOGIN_SOURCE = 'login_source';

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CreatesUserFromProvider $createsUser,
        protected CreatesConnectedAccounts $createsConnectedAccounts,
        protected UpdatesConnectedAccounts $updatesConnectedAccounts
    ) {
        //
    }



    /**
     * The page the Word add-in opens, for signing in and for signing up. The
     * add-in can only open a URL, so it puts its token in the fragment
     * (`#token=`), which browsers never send to the server; this page posts
     * it, so the token stays out of server, proxy and error logs.
     */
    public function showOfficeSsoBridge()
    {
        return view('office_sso_bridge', ['action' => url()->current()]);
    }

    public function handleOfficeSsoRegister(Request $request)
    {
        $token = $this->officeSsoToken($request);
        $officeSsoHelper = app(OfficeSsoHelper::class);

        try {
            $claims = $officeSsoHelper->validateIdToken($token);
        } catch (Throwable $e) {
            return $this->officeSsoFailed($e);
        }

        $user = $officeSsoHelper->findUser($claims);
        $email = $officeSsoHelper->verifiedEmail($claims);

        // Neither linked nor with an address the tenant verified: there is no
        // one to sign in or to create an account for.
        if ($user === null && $email === null) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=failed');
        }

        // The address belongs to an account that has not verified it: neither
        // link the Microsoft account to it nor create a second account.
        if ($user === null && User::where('email', $email)->exists()) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=unverified_email');
        }

        // Invite only: no account for an address nobody invited, and no
        // consent form for one either.
        if ($user === null && ! InviteOnly::allowsSignUp($email)) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=invite_only');
        }

        if (
            $user !== null
            && $user->is(Auth::user())
            && $user->has_consented_to_terms_of_service
        ) {
            return redirect(config('services.microsoft_office.redirect_uri') . '?status=success');
        }

        // The consent form always sends this field; the token alone, from the
        // add-in's page or from signing in, asks for the form.
        $errors = null;
        if ($request->has('has_consented_to_terms_of_service')) {
            // Not validate(): its redirect would go back to the add-in's page,
            // which has no token without the fragment. Show the form again.
            $validator = Validator::make($request->all(), [
                'has_consented_to_terms_of_service' => 'required|in:1',
                'has_consented_to_mailing' => 'nullable|boolean',
            ]);
            $validated = $validator->fails() ? false : $validator->validated();
            $errors = $validator->errors();
        } else {
            $validated = $user instanceof User && $user->has_consented_to_terms_of_service;
        }

        // not yet consented => show the form
        if (!$validated) {
            $params = [
                'name' => $claims['name'] ?? null,
                'email' => $user?->email ?? $email,
                'token' => $token,
            ];

            return response(
                view('office_register', $params)->with('errors', (new ViewErrorBag())->put('default', $errors ?? new MessageBag())),
                $errors?->isNotEmpty() ? 422 : 200
            );
        }

        $providerAccount = $officeSsoHelper->getProviderAccount($claims);
        if (is_array($validated) && $validated['has_consented_to_terms_of_service']) {
            $providerAccount->attributes['has_consented_to_terms_of_service'] = true;
            $providerAccount->attributes['has_consented_to_mailing'] = $validated['has_consented_to_mailing'] ?? false;
        }

        $this->handleProviderAccount($request, $providerAccount, self::OFFICE_PROVIDER);

        return redirect(config('services.microsoft_office.redirect_uri') . '?status=success');
    }

    public function handleOfficeSsoLogin(Request $request)
    {
        $token = $this->officeSsoToken($request);
        $officeSsoHelper = app(OfficeSsoHelper::class);

        try {
            $claims = $officeSsoHelper->validateIdToken($token);
        } catch (Throwable $e) {
            return $this->officeSsoFailed($e);
        }

        $user = $officeSsoHelper->findUser($claims);

        // no account for email that has consented to the terms => show the form
        if ($user === null || !$user->has_consented_to_terms_of_service) {
            return $this->handleOfficeSsoRegister($request);
        }

        // Anyone can be sent a link with someone else's token. Unless that
        // user is already signed in here, say who this signs in as and let
        // the person confirm, instead of switching accounts silently.
        if (!$user->is(Auth::user()) && !$request->boolean('confirm')) {
            return view('office_login_confirm', [
                'email' => $user->email,
                'signedInAs' => Auth::user()?->email,
                'token' => $token,
                'action' => url()->current(),
            ]);
        }

        // Switching accounts: nothing of the previous user's session stays.
        if (Auth::user() !== null && !$user->is(Auth::user())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Found by its verified email: link the Microsoft account, so that it
        // is recognised by its `sub` from now on.
        $this->handleProviderAccount($request, $officeSsoHelper->getProviderAccount($claims), self::OFFICE_PROVIDER);

        return $this->loginUser($user, self::OFFICE_PROVIDER);
    }

    /** The posted token; anything but a string counts as none. */
    protected function officeSsoToken(Request $request): string
    {
        $token = $request->input('token');

        return is_string($token) ? $token : '';
    }

    /** A refused token: logged without the token itself, which is a credential. */
    protected function officeSsoFailed(Throwable $e)
    {
        Log::info('Office sign-in refused a token', ['exception' => $e::class, 'reason' => strtok($e->getMessage(), "\n")]);

        return redirect(config('services.microsoft_office.redirect_uri') . '?status=failed');
    }

    protected function handleProviderAccount(Request $request, $providerAccount, string $provider)
    {
        $userData = [];

        if (!empty($providerAccount->attributes['has_consented_to_terms_of_service'])) {
            $userData['has_consented_to_terms_of_service'] = $providerAccount->attributes['has_consented_to_terms_of_service'];
        }

        $account = ConnectedAccount::where('provider', $provider)
            ->where('provider_id', $providerAccount->getId())
            ->first();

        // Left behind by a deleted user: it links nobody.
        if ($account && !$account->user) {
            $account->delete();
            $account = null;
        }

        if (!$account) {
            $user = User::where('email', $providerAccount->getEmail())
                ->whereNotNull('email_verified_at')
                ->first();
            if ($user) {
                $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
            } else {
                $user = $this->createsUser->create($provider, $providerAccount);
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


    /**
     * Mirrors the response socialstream produced for a Jetstream application:
     * a JSON body for XHR callers, otherwise Fortify's intended login redirect.
     */
    protected function loginUser(Authenticatable $user, $provider): JsonResponse|RedirectResponse
    {
        Auth::login($user, config('social_auth.remember_session', true));
        // A new session id for the signed-in user, against session fixation.
        request()->session()->regenerate();
        request()->session()->put(self::LOGIN_SOURCE, $provider);

        return request()->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(Fortify::redirects('login'));
    }


    // mockLogin feature removed

    /**
     * Entry point for the extension's "Sign in" / "Sign up" buttons.
     *
     * Issues nothing itself — it is a redirect into /oauth/authorize, which runs
     * the real authorization_code + PKCE flow. (It used to mint an HS256 JWT
     * signed with APP_KEY that nothing ever validated: the extension API only
     * accepted Microsoft id_tokens, so every call made with one of those JWTs
     * was rejected with a 403.)
     *
     * The only thing this adds over linking straight to /oauth/authorize is the
     * `register` case. Passport's authorization endpoint sends guests to the
     * login page; a user who came from "Sign up" wants the registration page,
     * and there is no OAuth parameter for that.
     */
    public function browserLogin(Request $request)
    {
        // Every parameter except our own `register` flag belongs to the OAuth
        // request — response_type, client_id, redirect_uri, code_challenge,
        // code_challenge_method, state — and is passed through untouched. The
        // destination is our own named route, so nothing here can be redirected
        // off-site; validating the OAuth parameters is the authorization
        // endpoint's job, not ours.
        $authorizeUrl = route('passport.authorizations.authorize', $request->except('register'));

        if ($request->boolean('register') && !Auth::check()) {
            // Fortify sends the user to fortify.home after registering, so the
            // OAuth request has to be stashed as the intended URL to come back
            // to. redirect()->guest() would stash this route instead, which
            // would bounce the user through here a second time.
            $request->session()->put('url.intended', $authorizeUrl);

            return redirect()->route('register');
        }

        // Guests get here too: AuthorizationController throws an
        // AuthenticationException, which the handler turns into a redirect to
        // the login page with url.intended set, and back here afterwards.
        return redirect()->to($authorizeUrl);
    }
}
