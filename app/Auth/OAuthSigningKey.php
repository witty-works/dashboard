<?php

namespace App\Auth;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Passport;
use RuntimeException;

/**
 * The public half of Passport's token signing key, as a JWK.
 *
 * Single source of truth for the key ID on purpose: the `kid` in an access
 * token's header and the `kid` in /.well-known/jwks.json have to be the same
 * string or every consumer's key lookup fails, and they are produced in two
 * very different places (App\Auth\AccessToken and JwksController).
 */
class OAuthSigningKey
{
    /**
     * @return array<string, string>
     */
    public function jwk(): array
    {
        // The key only changes when someone runs `php artisan passport:keys`,
        // which invalidates every issued token anyway. Cached because this is
        // now on the hot path — every token issued reads it for the kid.
        return Cache::remember('oauth.signing-key.jwk', now()->addHour(), function () {
            return $this->buildJwk();
        });
    }

    public function kid(): string
    {
        return $this->jwk()['kid'];
    }

    /**
     * @return array<string, string>
     */
    protected function buildJwk(): array
    {
        $key = openssl_pkey_get_public($this->publicKeyContents());

        if ($key === false) {
            throw new RuntimeException('Passport public key could not be parsed.');
        }

        $details = openssl_pkey_get_details($key);

        if (! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException('Passport public key is not an RSA key.');
        }

        $n = $this->base64Url($details['rsa']['n']);
        $e = $this->base64Url($details['rsa']['e']);

        return [
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            // RFC 7638 thumbprint. The member order and the absence of
            // whitespace are both required by the spec — do not "tidy" this.
            'kid' => $this->base64Url(hash('sha256', json_encode([
                'e' => $e,
                'kty' => 'RSA',
                'n' => $n,
            ]), true)),
            'n' => $n,
            'e' => $e,
        ];
    }

    /**
     * Resolve the public key the same way PassportServiceProvider::makeCryptKey()
     * does: the configured value first, falling back to the file in storage.
     */
    protected function publicKeyContents(): string
    {
        $configured = str_replace('\\n', "\n", (string) config('passport.public_key'));

        if ($configured !== '') {
            return $configured;
        }

        $path = Passport::keyPath('oauth-public.key');

        if (! is_readable($path)) {
            throw new RuntimeException(
                "Passport public key not found at {$path}. Run `php artisan passport:keys`."
            );
        }

        return file_get_contents($path);
    }

    protected function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
