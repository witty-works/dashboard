<?php

namespace App\Auth;

/**
 * The `iss` value for tokens this application issues.
 *
 * Single source of truth on purpose, for the same reason as
 * [OAuthSigningKey]: the issuer is a string two sides have to agree on
 * byte-for-byte — we put it in the token, the NLP API compares it against its
 * configured DASHBOARD_ISSUER — and the moment it is rebuilt in a second place
 * the two can drift. Anything that needs to name this issuer (a discovery
 * document at /.well-known/openid-configuration, for instance) should read it
 * from here rather than reaching for config('app.url') again.
 *
 * @see \App\Auth\OAuthSigningKey the same pattern, for the key ID
 */
class OAuthIssuer
{
    public function value(): string
    {
        // Trailing slash matters: PyJWT compares `iss` with plain string
        // equality, so "https://host" and "https://host/" are different
        // issuers. APP_URL is written both ways across our environments —
        // .env.example carries the slash, the local .env does not — so the
        // token must not simply echo it back.
        return rtrim((string) config('app.url'), '/');
    }
}
