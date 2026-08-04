# OAuth2 / API authentication

Since Azure AD B2C was removed (`d4e2f683`), sign-in to the dashboard is local
username/password via Fortify. This document covers the other half: how the
browser extensions and the Office add-in authenticate against the API.

This is the dashboard side of Phase 1 in `AUTH_SECURITY_PLAN.md`, which lives in
the `browser-extension` repo (`~/htdocs/browser-extension`), not here.

Verified end to end against a running instance: the guest redirect, the full
code-for-token exchange, single-use codes, PKCE enforcement, exact redirect-URI
matching, the refresh grant, the rate limiter, logout revocation, and a token
verifying against the published JWKS.

Phase 1 is complete. Both the Chrome/Opera and Firefox redirect URIs are
registered, and each has been driven through a full code-for-token exchange.

## What clients exist

| Client | Authenticates with | Issued by |
| --- | --- | --- |
| Dashboard web UI | Session cookie (`web` / `sanctum` guard) | Fortify |
| Browser extensions (Chrome, Opera, Edge, Firefox) | Passport access token, RS256 | `/oauth/token` |
| Office add-in | Microsoft id_token | The Office host, not us |

The extension API uses the `extension` guard, which accepts either of the last
two — see `App\Auth\ExtensionUserResolver`.

## Endpoints

| Endpoint | Auth | Purpose |
| --- | --- | --- |
| `GET /oauth/authorize` | session | Start the flow; sends guests to the login page |
| `POST /oauth/token` | PKCE | `authorization_code` and `refresh_token` grants |
| `GET /browser-login` | none | Thin redirect for the extension's Sign in / Sign up buttons |
| `GET /.well-known/jwks.json` | none | Public key, for the NLP API to verify tokens |
| `GET /api/userinfo` | bearer | `{id, email, name}` of the token's owner |
| `PUT/DELETE /api/user/language/*` | bearer | Guidelines, as before |

## The extension flow

Standard OAuth2 authorization code with PKCE. The extension is a **public
client**: it has no secret, because anything shipped in a store bundle is
readable by anyone who unzips it. PKCE is what binds the token exchange to the
client that started the flow.

```
1. Extension generates code_verifier, derives
   code_challenge = base64url(sha256(code_verifier))

2. Opens a browser tab (browser.identity.launchWebAuthFlow):

   GET /oauth/authorize
     ?response_type=code
     &client_id=<EXTENSION_OAUTH_CLIENT_ID>
     &redirect_uri=<registered redirect uri>
     &code_challenge=<code_challenge>
     &code_challenge_method=S256
     &state=<random, checked on return>

3. Not signed in?  Fortify login screen, then back here via url.intended.
   Signed in?      No consent screen — the client is first-party.

4. Redirect to redirect_uri?code=<code>&state=<state>

5. POST /oauth/token          (no cookies, no CSRF, throttled to 10/min per IP)
     grant_type=authorization_code
     client_id=<EXTENSION_OAUTH_CLIENT_ID>
     redirect_uri=<same as step 2>
     code=<code>
     code_verifier=<code_verifier>

   -> { token_type, expires_in, access_token, refresh_token }

6. Call the API with  Authorization: Bearer <access_token>

7. On 401, refresh:
   POST /oauth/token
     grant_type=refresh_token
     refresh_token=<refresh_token>
     client_id=<EXTENSION_OAUTH_CLIENT_ID>
```

Access tokens live 1 hour, refresh tokens 30 days
(`PASSPORT_ACCESS_TOKEN_TTL` / `PASSPORT_REFRESH_TOKEN_TTL`). When the refresh
token expires the extension must send the user through step 2 again.

The access token is an RS256 JWT with `aud` = the client ID, `sub` = the user ID
and a `kid` header matching the JWK Set. It carries no email claim —
league/oauth2-server builds the JWT and Passport exposes no hook for extra
claims. `GET /api/userinfo` serves it instead, which also means a renamed user is
reflected immediately rather than at the next token refresh.

league/oauth2-server emits no `kid` of its own, and without one neither
firebase/php-jwt's `JWK::parseKeySet()` nor PyJWT's `PyJWKClient` can look the key
up — both fail outright. `App\Auth\AccessToken` adds it.

### Sign in / Sign up entry point

`GET /browser-login` exists only so the extension's two buttons can differ. It
issues nothing; it forwards every query parameter to `/oauth/authorize`, except
that `register=1` on a signed-out request sends the user to the registration page
first, with the authorize URL stashed as `url.intended`. There is no OAuth
parameter for "I want to sign up", hence the shim.

### Unauthenticated users are redirected, never 401'd

A guest hitting `/oauth/authorize` gets `302` to the Fortify login page with the
full authorize URL — PKCE parameters and all — in `url.intended`, and lands back
on the authorization endpoint after signing in. Verified end to end.

This is why `GET /oauth/authorize` deliberately carries no `auth` middleware:
`AuthorizationController` handles guests itself, throwing an
`AuthenticationException` that the handler renders as a redirect for any request
that is not `expectsJson()`. A JSON 401 here would dead-end the flow, which is
what the interim `browserLogin` did.

### Logging out revokes extension tokens

Logging out of the dashboard revokes every OAuth access and refresh token the
user holds (`App\Listeners\RevokeOAuthTokens`, on `Illuminate\Auth\Events\Logout`).
Otherwise "log out" would only drop the browser session while the extension kept
a live access token and a 30-day refresh token — not what anyone means by logging
out, least of all on a shared machine.

The listener only fires for an actual POST to Fortify's `logout` route. That
check matters: the Office SSO handler calls `Auth::logout()` to clear the session
before signing the add-in user in, and revoking there would disconnect the
extension as a side effect of opening the Word add-in.

> **This is not visible to the NLP API.** Revocation is a database flag, and the
> dashboard's own guard checks it on every request. A service validating the JWT
> offline against the JWK Set sees only the signature and `exp`, so a revoked
> token stays acceptable there until it expires — up to `PASSPORT_ACCESS_TOKEN_TTL`
> (1 hour). That window is the price of offline validation. If Phase 3 needs
> revocation to be immediate, the NLP API has to call back to the dashboard, and
> the TTL should be cut in the meantime.

### Redirect URIs

league/oauth2-server compares the `redirect_uri` against the registered list with
a strict `in_array()` — byte-for-byte, no prefix or wildcard matching. Use
whatever `browser.identity.getRedirectURL()` returns:

Both are registered and verified working:

| Browser | Redirect URL |
| --- | --- |
| Chrome / Opera | `https://meojhlodfiihbjkcnehkdcgncnhgagog.chromiumapp.org/` |
| Firefox | `https://e74fe0942ca5c6c17185e1e4d5798b0ecd5a36d4.extensions.allizom.org/` |

The Chrome value derives from the pinned `manifest.json` key. **The Firefox value
does not need to be read from a running add-on** — Firefox computes it as a plain
SHA-1 of the extension ID (`toolkit/components/extensions/child/ext-identity.js`):

```js
const computeHash = str => {
  let byteArr = new TextEncoder().encode(str);
  let hash = new CryptoHash("sha1");
  hash.update(byteArr, byteArr.length);
  return CommonUtils.bytesAsHex(hash.finish(false));
};
// https://${computeHash(extension.id)}.${redirectDomain}/
```

So, with the braces, exactly as the ID appears in the manifest:

```sh
printf '%s' '{4b376457-9460-4891-b28a-60499c2e3343}' | shasum -a 1
# e74fe0942ca5c6c17185e1e4d5798b0ecd5a36d4
```

Verified against three published extensions before trusting it —
`notes@mozilla.com` and `lockbox@mozilla.com` (both registered in `mozilla/fxa`)
and the braced-UUID case `{8d4b86c5-…}` from `advanced-github-notifier`. All three
reproduce exactly.

Because the ID is pinned in `browser_specific_settings.gecko.id`, this value is
stable across installs and profiles. It changes only if that ID changes.

> Never relax this to a `moz-extension://` prefix match the way the old
> `checkAllowedRedirectUri` did with `strpos(…) === 0`. The client ID is public by
> definition, so a prefix match hands an access token to any extension that asks.

## Setup

Per environment, once:

```sh
php artisan migrate          # creates the five oauth_* tables
php artisan passport:keys    # writes storage/oauth-{private,public}.key
```

Passport 12 only *publishes* its migrations, it does not load them from the
package, so the five `2016_06_01_*_create_oauth_*` files are committed to
`database/migrations` in this repo. `vendor:publish --tag=passport-migrations`
has already been run; do not run it again or you will get duplicates.

`storage/*.key` is gitignored. Where the filesystem is ephemeral, set
`PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` instead of shipping the files.
**Regenerating these keys invalidates every issued token**, forcing every
extension user to sign in again.

Then provision the client:

```sh
# set EXTENSION_OAUTH_REDIRECT_URIS first
php artisan passport:extension-client
```

This is `passport:client --public` plus idempotency — given
`EXTENSION_OAUTH_CLIENT_ID` it updates that client in place instead of creating a
second one, so it is safe on every deploy. That matters because the client ID is
baked into the shipped extension and cannot change without a store release.

It prints the client ID. Put it in `EXTENSION_OAUTH_CLIENT_ID` and in
`PASSPORT_FIRST_PARTY_CLIENTS` (the latter is what skips the consent screen),
then into `oauth_client_id` on each `BASE_URLS` entry in the extension's
`witty.config.json`.

## Layout

| Path | Role |
| --- | --- |
| `routes/oauth.php` | The four `/oauth` routes we serve |
| `routes/wellknown.php` | `/.well-known/jwks.json` |
| `config/passport.php` | Client provisioning, first-party list, TTLs, key overrides |
| `app/Providers/AuthServiceProvider.php` | `Passport::ignoreRoutes()`, TTLs, `extension` guard |
| `app/Auth/ExtensionUserResolver.php` | Passport token, else Microsoft id_token |
| `app/Models/OAuthClient.php` | Consent-screen skip for first-party clients |
| `app/Auth/OAuthSigningKey.php` | The signing key as a JWK; the one source of `kid` |
| `app/Auth/AccessToken.php` | Token entity, adds the `kid` header |
| `app/Http/Controllers/JwksController.php` | Public key as a JWK Set |
| `app/Http/Controllers/UserInfoController.php` | `/api/userinfo` |
| `app/Listeners/RevokeOAuthTokens.php` | Revokes tokens on dashboard logout |
| `app/Console/Commands/CreateExtensionOAuthClientCommand.php` | `passport:extension-client` |

Two pieces of wiring are load-order sensitive and will silently break if moved
into a `boot()` method — package providers boot before application ones:

- `AuthServiceProvider::register()` calls `Passport::ignoreRoutes()` so we own
  the route definitions. Passport's own file throttles `/oauth/token` at 60/min
  and exposes client-management endpoints this app has no UI for.
- `SocialstreamServiceProvider::register()` sets
  `Socialstream::$registersRoutes = false`. Socialstream declares a
  `GET oauth/{provider}` wildcard that is registered *before* Passport's routes
  and would otherwise swallow `GET oauth/authorize`. Its provider list has been
  empty since Azure AD B2C was removed, so it serves nothing.

## Not done

- **The NLP API still has to be taught these tokens.** It currently decodes
  bearer tokens against Azure AD B2C's JWKS and matches `aud` against its
  configured `client_id`. Passport tokens are RS256, verifiable with the key at
  `/.well-known/jwks.json`, and their `aud` is the OAuth client ID (`1` in dev).
  A stock JWKS client works — `PyJWKClient(f"{base}/.well-known/jwks.json")` then
  `jwt.decode(token, key, algorithms=["RS256"], audience=client_id)`. That is an
  `nlp_api` change, outside this repo.
- **No scopes.** One client, one purpose, so every token carries full user
  access. Adding `Passport::tokensCan()` and `scopes:` middleware later needs the
  `extension` guard adjusted, since Office-SSO users have no Passport token for
  that middleware to inspect.
- **No "connected apps" UI.** `/oauth/tokens` and `/oauth/clients` are
  deliberately not routed, so a user cannot see or individually revoke a
  connection — logging out is all-or-nothing. Worth adding if a second client
  ever appears.
- **Client secrets are not hashed.** `Passport::hashClientSecrets()` is off. It
  costs nothing today because the only client is public and has no secret, but it
  should be enabled before the first confidential client is created.
