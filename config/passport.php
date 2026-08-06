<?php

return [

    /*
    |--------------------------------------------------------------------------
    | First-Party Clients
    |--------------------------------------------------------------------------
    |
    | Client IDs listed here skip the OAuth consent screen — see
    | App\Models\OAuthClient::skipsAuthorization(). Only put clients we ship
    | ourselves in here; anything third-party must keep the prompt.
    |
    | Comma separated, e.g. PASSPORT_FIRST_PARTY_CLIENTS="1,2".
    |
    */

    'first_party_clients' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('PASSPORT_FIRST_PARTY_CLIENTS', '')))
    )),

    /*
    |--------------------------------------------------------------------------
    | Auto-granted Scopes
    |--------------------------------------------------------------------------
    |
    | Scopes a first-party client may receive without the consent screen. Empty
    | by default, and the application defines no scopes at all today, so an
    | ordinary request asks for nothing and skips the prompt as before.
    |
    | This exists so that introducing a scope cannot silently widen what a
    | first-party client is handed. A request carrying a scope that is not listed
    | here goes through the normal consent path instead of being waved through on
    | the strength of the client's identity alone.
    |
    | See App\Models\OAuthClient::skipsAuthorization().
    |
    */

    'first_party_scopes' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('PASSPORT_FIRST_PARTY_SCOPES', '')))
    )),

    /*
    |--------------------------------------------------------------------------
    | Browser Extension Client
    |--------------------------------------------------------------------------
    |
    | Provisioning details for the public (secret-less) authorization_code +
    | PKCE client used by the Chrome/Edge/Firefox extensions. Consumed by
    | `php artisan passport:extension-client`, which is idempotent so it can run
    | on every deploy.
    |
    | The redirect URIs must match byte-for-byte what the extension sends —
    | league/oauth2-server compares them with a strict in_array(). Use the value
    | browser.identity.getRedirectURL() returns, which is stable per extension:
    |
    |   Chrome / Edge  https://<extension-id>.chromiumapp.org/
    |   Firefox        https://<extension-uuid>.extensions.allizom.org/
    |
    | Firefox only keeps that UUID stable when the extension pins
    | browser_specific_settings.gecko.id in its manifest. Do not be tempted to
    | relax this into a moz-extension:// prefix match (as the Azure AD B2C code
    | used to do) — that hands an access token to any extension that guesses the
    | client ID.
    |
    */

    'extension' => [
        'name' => env('EXTENSION_OAUTH_CLIENT_NAME', 'Witty Browser Extension'),
        'client_id' => env('EXTENSION_OAUTH_CLIENT_ID'),
        'redirect_uris' => array_values(array_filter(
            array_map('trim', explode(',', (string) env('EXTENSION_OAUTH_REDIRECT_URIS', '')))
        )),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Lifetimes
    |--------------------------------------------------------------------------
    |
    | Access tokens are deliberately short: the extension holds them in
    | storage that any code running in the browser profile can reach, and a
    | refresh token exchange is cheap. Refresh tokens must outlive the access
    | token or the extension can never recover without a full re-login.
    |
    */

    'access_token_ttl' => env('PASSPORT_ACCESS_TOKEN_TTL', 'PT1H'),
    'refresh_token_ttl' => env('PASSPORT_REFRESH_TOKEN_TTL', 'P30D'),

    /*
    |--------------------------------------------------------------------------
    | Token Signing Keys
    |--------------------------------------------------------------------------
    |
    | Passport reads these two keys through config() and only falls back to
    | storage/oauth-{private,public}.key when they are empty, so they have to be
    | declared here for the environment variables to have any effect at all.
    | Useful where the filesystem is ephemeral; `\n` in the value is unescaped
    | by Passport itself.
    |
    | Access tokens are RS256, which is the point: the NLP API verifies them
    | with the public key published at /.well-known/jwks.json and no private key
    | material ever has to be copied between services.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),
    'public_key' => env('PASSPORT_PUBLIC_KEY'),

];
