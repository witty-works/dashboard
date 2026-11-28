<?php

namespace Tests\Feature;

use App\Auth\AccessToken;
use App\Auth\OAuthSigningKey;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Passport;
use League\OAuth2\Server\CryptKey;
use Tests\TestCase;

/**
 * Covers the claims App\Auth\AccessToken adds on top of league/oauth2-server,
 * each of which exists for a consumer that breaks without it: `kid` so a stock
 * JWKS client can find the key, `iss` so a consumer checking the issuer does not
 * reject the token outright, `email_verified` so the NLP API can refuse tokens
 * of users who never confirmed their address.
 *
 * These assert on a token built through the real entity rather than on a mocked
 * builder, because the failure mode being guarded against is precisely that the
 * override stops being called — league 9 renamed __toString() to toString(), and
 * an override of the old name fails silently, emitting a valid token with none
 * of these claims.
 */
class AccessTokenClaimsTest extends TestCase
{
    use RefreshDatabase;

    private function issueToken(?string $userIdentifier = null): array
    {
        $keyPath = Passport::keyPath('oauth-private.key');

        if (! file_exists($keyPath)) {
            $this->markTestSkipped('Passport keys are missing; run `php artisan passport:keys`.');
        }

        // No user identifier by default: user() then short-circuits and the
        // token carries no email claims. Those are covered by the user tests below.
        $token = new AccessToken(
            $userIdentifier,
            [],
            new Client('client-id', 'Test Client', ['https://example.test/'], false)
        );

        $token->setIdentifier('test-token-identifier');
        $token->setExpiryDateTime(new DateTimeImmutable('+1 hour'));
        $token->setPrivateKey(new CryptKey($keyPath, null, false));

        [$header, $payload] = explode('.', $token->toString());

        return [
            'header' => $this->decodeSegment($header),
            'payload' => $this->decodeSegment($payload),
        ];
    }

    private function decodeSegment(string $segment): array
    {
        return json_decode(
            base64_decode(str_pad(strtr($segment, '-_', '+/'), strlen($segment) % 4, '=', STR_PAD_RIGHT)),
            true
        );
    }

    public function test_it_issues_the_configured_issuer(): void
    {
        config(['app.url' => 'https://dashboard.example.test/']);

        $claims = $this->issueToken()['payload'];

        // Trailing slash stripped, matching what DASHBOARD_ISSUER must be set to.
        $this->assertSame('https://dashboard.example.test', $claims['iss']);
    }

    public function test_it_carries_the_key_id_of_the_published_jwks_entry(): void
    {
        $header = $this->issueToken()['header'];

        $this->assertSame(app(OAuthSigningKey::class)->kid(), $header['kid']);
        $this->assertSame('RS256', $header['alg']);
    }

    public function test_it_still_emits_the_claims_league_provides(): void
    {
        $claims = $this->issueToken()['payload'];

        // Guards against the override drifting from the trait it replaces: the
        // NLP API dispatches on `aud`, so losing it would break token lookup.
        $this->assertSame('client-id', $claims['aud']);
        $this->assertSame('test-token-identifier', $claims['jti']);
        $this->assertSame([], $claims['scopes']);
        $this->assertArrayHasKey('exp', $claims);
    }

    public function test_a_userless_token_falls_back_to_the_client_as_subject(): void
    {
        $claims = $this->issueToken()['payload'];

        // Mirrors the trait's own getSubjectIdentifier(); an empty `sub` would be
        // a silent regression rather than an error.
        $this->assertSame('client-id', $claims['sub']);
        $this->assertArrayNotHasKey('email', $claims);
    }

    public function test_a_verified_user_token_says_the_email_is_verified(): void
    {
        $user = User::factory()->create();

        $claims = $this->issueToken((string) $user->id)['payload'];

        $this->assertSame($user->email, $claims['email']);
        $this->assertSame($user->email, $claims['preferred_username']);
        $this->assertTrue($claims['email_verified']);
    }

    public function test_an_unverified_user_token_says_the_email_is_not_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $claims = $this->issueToken((string) $user->id)['payload'];

        $this->assertSame($user->email, $claims['email']);
        // Strictly false, not absent: the NLP API checks for `=== true`, and an
        // explicit false says the dashboard knows the address is unconfirmed.
        $this->assertFalse($claims['email_verified']);
    }

    public function test_a_userless_token_has_no_email_verified_claim(): void
    {
        $claims = $this->issueToken()['payload'];

        $this->assertArrayNotHasKey('email_verified', $claims);
    }
}
