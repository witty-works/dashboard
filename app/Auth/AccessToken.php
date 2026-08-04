<?php

namespace App\Auth;

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

        return $this->jwtConfiguration->builder()
            ->withHeader('kid', app(OAuthSigningKey::class)->kid())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ->toString();
    }
}
