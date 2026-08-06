<?php

namespace App\Auth;

use App\Helpers\OfficeSsoHelper;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

/**
 * Resolves the user behind a bearer token on the extension API.
 *
 * Two unrelated clients hit those routes and neither can be made to look like
 * the other:
 *
 *  - the browser extension, which runs the authorization_code + PKCE flow
 *    against /oauth/authorize and /oauth/token and presents a Passport token;
 *  - the Office add-in, which never talks to our authorization server at all —
 *    the Office host hands it a Microsoft-issued id_token, which we validate
 *    against Entra's JWKS.
 *
 * Registered as the "extension" guard in App\Providers\AuthServiceProvider.
 */
class ExtensionUserResolver
{
    public function __construct(
        protected AuthFactory $auth,
        protected OfficeSsoHelper $officeSso
    ) {
    }

    public function __invoke(Request $request): ?Authenticatable
    {
        // Read before anything else touches the request. When Passport's guard
        // cannot validate a bearer token it blanks the Authorization header
        // (TokenGuard::getPsrRequestViaBearerToken), so a later
        // $request->bearerToken() returns null.
        $token = $request->bearerToken();

        if (empty($token)) {
            return null;
        }

        // Dispatch on the audience rather than trying Passport and falling
        // through. An Office add-in token is issued by Microsoft and can only
        // ever fail Passport's validation, and that failure is not free: the
        // guard converts the request to PSR-7, runs a full validation pass, and
        // then hands the exception to the exception handler — which in
        // production means a Sentry event for every add-in request.
        if ($this->looksLikeOfficeSsoToken($token)) {
            return $this->resolveOfficeSsoUser($token);
        }

        // Browser extension: an access token minted by our own /oauth/token.
        return $this->auth->guard('api')->user();
    }

    /**
     * Whether the token claims to be for the Office add-in.
     *
     * Routing only — the claim is read without any signature check, so nothing
     * here is trusted. A forged audience just sends the token down a path that
     * verifies it against Microsoft's keys and rejects it.
     */
    protected function looksLikeOfficeSsoToken(string $token): bool
    {
        $clientId = config('services.microsoft_office.client_id');

        // With no Office client configured there is no such thing as an Office
        // token. Returning early also stops the audience check below from
        // degenerating into `null === null` for a token with no aud claim.
        if (empty($clientId)) {
            return false;
        }

        try {
            $payload = $this->officeSso->decodeIdToken($token);
        } catch (Exception $e) {
            return false;
        }

        return ($payload['aud'] ?? null) === $clientId;
    }

    /**
     * Validate a Microsoft id_token and map it onto a local user.
     */
    protected function resolveOfficeSsoUser(string $token): ?Authenticatable
    {
        try {
            // This is the call that actually verifies signature, issuer,
            // audience and expiry.
            $claims = $this->officeSso->validateIdToken($token);
        } catch (Exception $e) {
            return null;
        }

        $email = strtolower($claims['preferred_username'] ?? '');

        if ($email === '') {
            return null;
        }

        return User::where('email', $email)->first();
    }
}
