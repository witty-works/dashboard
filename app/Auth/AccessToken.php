<?php

namespace App\Auth;

use App\Models\User;
use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Passport's access token entity, with a `kid` header and email claims.
 *
 * Two things league/oauth2-server does not emit, each of which a consumer
 * needs:
 *
 * - **`kid`**: without it no off-the-shelf JWKS client can verify our tokens.
 *   Both firebase/php-jwt's JWK::parseKeySet() and PyJWT's PyJWKClient look the
 *   key up by `kid` and fail outright when the header has none.
 * - **`email`**: the NLP API identifies users by email; `sub` is our local user
 *   id and means nothing to it.
 *
 * This re-uses AccessTokenTrait rather than extending Passport's
 * Bridge\AccessToken: the trait's $jwtConfiguration and $privateKey are private,
 * so a subclass could not reach them, but a class that uses the trait itself
 * can. Defining toString() here overrides the trait's copy — the body is the
 * trait's private convertToJWT() plus the additions above.
 *
 * Note the method name. league/oauth2-server 8 spelled this `__toString()`; 9
 * renamed it to `toString()`. The rename is silent for anyone overriding the old
 * name — the trait's own method simply keeps winning, and tokens go out without
 * any of the above.
 *
 * @see \Laravel\Passport\Bridge\AccessToken the class this replaces
 */
class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    /**
     * @param  non-empty-string|null  $userIdentifier
     * @param  \League\OAuth2\Server\Entities\ScopeEntityInterface[]  $scopes
     */
    public function __construct(?string $userIdentifier, array $scopes, ClientEntityInterface $client)
    {
        // Guarded rather than called unconditionally: setUserIdentifier() is
        // typed non-empty-string in league 9, so passing the null of a
        // client_credentials token through it is a TypeError.
        if (! is_null($userIdentifier)) {
            $this->setUserIdentifier($userIdentifier);
        }

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }

        $this->setClient($client);
    }

    public function toString(): string
    {
        $this->initJwtConfiguration();

        $builder = $this->jwtConfiguration->builder()
            ->withHeader('kid', app(OAuthSigningKey::class)->kid())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            // Matches the trait's own getSubjectIdentifier(): the user for a
            // user token, the client for a client_credentials one, never an
            // empty `sub`.
            ->relatedTo($this->getUserIdentifier() ?? $this->getClient()->getIdentifier())
            ->withClaim('scopes', $this->getScopes());

        // Emitted under two names on purpose: `email` is the standard OIDC
        // claim, and `preferred_username` is what the NLP API's existing
        // fetch_email_from_claims() already reads off Azure AD B2C tokens, so
        // that side needs no extraction changes.
        if ($email = $this->userEmail()) {
            $builder = $builder
                ->withClaim('email', $email)
                ->withClaim('preferred_username', $email);
        }

        return $builder
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ->toString();
    }

    /**
     * One query per token issued — i.e. once an hour per signed-in user, on a
     * path that is already doing RSA signing.
     */
    protected function userEmail(): ?string
    {
        $userId = $this->getUserIdentifier();

        // No user identifier means a client_credentials token, which belongs to
        // no one. We do not issue those today, but the claim must not be faked
        // if we ever do.
        if ($userId === null) {
            return null;
        }

        return User::find($userId)?->email;
    }
}
