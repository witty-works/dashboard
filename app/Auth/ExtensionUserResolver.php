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
        $token = $request->bearerToken();

        if (empty($token)) {
            return null;
        }

        // Browser extension: an access token minted by our own /oauth/token.
        if ($user = $this->auth->guard('api')->user()) {
            return $user;
        }

        return $this->resolveOfficeSsoUser($token);
    }

    /**
     * Validate a Microsoft id_token and map it onto a local user.
     */
    protected function resolveOfficeSsoUser(string $token): ?Authenticatable
    {
        $clientId = config('services.microsoft_office.client_id');

        // With no Office client configured there is no such thing as a valid
        // Office token. Returning early also stops the audience check below
        // from degenerating into `null === null` for a token that carries no
        // aud claim at all.
        if (empty($clientId)) {
            return null;
        }

        try {
            $payload = $this->officeSso->decodeIdToken($token);
        } catch (Exception $e) {
            return null;
        }

        // decodeIdToken() only base64-decodes; nothing is trusted yet. This
        // check exists so that a Passport token that merely failed to resolve
        // does not cost us a JWKS round-trip to Microsoft.
        if (($payload['aud'] ?? null) !== $clientId) {
            return null;
        }

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
