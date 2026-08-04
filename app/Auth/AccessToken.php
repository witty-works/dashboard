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
 * Passport's access token entity, with a `kid` in the JWT header.
 *
 * league/oauth2-server emits no `kid`, which makes the token unusable with an
 * off-the-shelf JWKS client: both firebase/php-jwt's JWK::parseKeySet() and
 * PyJWT's PyJWKClient look the key up by `kid` and fail outright when the header
 * has none. Since the NLP API is meant to verify these tokens the same way it
 * already verifies Microsoft's, the header has to carry one.
 *
 * This deliberately re-uses AccessTokenTrait rather than extending Passport's
 * Bridge\AccessToken: the trait's $jwtConfiguration and $privateKey are private,
 * so a subclass could not reach them, but a class that uses the trait itself
 * can. Defining __toString() here overrides the trait's copy — the body is the
 * trait's private convertToJWT() plus the one extra header.
 *
 * @see \Laravel\Passport\Bridge\AccessToken the class this replaces
 */
class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    /**
     * @param  string  $userIdentifier
     * @param  \League\OAuth2\Server\Entities\ScopeEntityInterface[]  $scopes
     */
    public function __construct($userIdentifier, array $scopes, ClientEntityInterface $client)
    {
        $this->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }

        $this->setClient($client);
    }

    public function __toString(): string
    {
        $this->initJwtConfiguration();

        $builder = $this->jwtConfiguration->builder()
            ->withHeader('kid', app(OAuthSigningKey::class)->kid())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        // The NLP API identifies a user by email, and `sub` carries only our
        // local user id — meaningless to it. Emitted under two names on purpose:
        // `email` is the standard OIDC claim, and `preferred_username` is what
        // the NLP API's existing fetch_email_from_claims() already reads off
        // Azure AD B2C tokens, so that side needs no extraction changes.
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
