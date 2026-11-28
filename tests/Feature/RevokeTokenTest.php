<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * POST /api/user/revoke-token, the extension's "Sign out".
 *
 * Tokens are issued through the real authorization_code + PKCE flow rather than
 * inserted by hand, because the case that matters is the refresh grant: league's
 * refresh grant ignores the access token's `revoked` flag, so only a revoked
 * refresh token stops the extension from signing itself back in.
 */
class RevokeTokenTest extends TestCase
{
    use RefreshDatabase;

    private const REDIRECT_URI = 'https://example.test/callback';

    private OAuthClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(Passport::keyPath('oauth-private.key'))) {
            $this->markTestSkipped('Passport keys are missing; run `php artisan passport:keys`.');
        }

        $this->client = new OAuthClient();
        $this->client->forceFill([
            'name' => 'Witty Browser Extension',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => [self::REDIRECT_URI],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();

        // First-party, so /oauth/authorize redirects straight back with a code.
        config(['passport.first_party_clients' => [(string) $this->client->getKey()]]);
    }

    /**
     * One sign-in of the extension, as on one device.
     *
     * @return array{access_token: string, refresh_token: string}
     */
    private function signIn(User $user): array
    {
        $verifier = Str::random(64);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $redirect = $this->actingAs($user)
            ->withoutLocaleRedirects()
            ->get('/oauth/authorize?' . http_build_query([
                'client_id' => $this->client->getKey(),
                'redirect_uri' => self::REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test-state',
                'code_challenge' => $challenge,
                'code_challenge_method' => 'S256',
            ]))
            ->assertRedirect()
            ->headers->get('Location');

        parse_str((string) parse_url($redirect, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('code', $query, "No code in the authorize redirect: $redirect");

        $tokens = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->getKey(),
            'redirect_uri' => self::REDIRECT_URI,
            'code' => $query['code'],
            'code_verifier' => $verifier,
        ])->assertOk()->json();

        $this->forgetAuthentication();

        return $tokens;
    }

    /**
     * Each request in a test runs in the same application, and the guards cache
     * the user they resolved. Without this a revoked token would still look
     * signed in on the next request, and the session from signIn() would
     * authenticate requests that carry no token at all.
     */
    private function forgetAuthentication(): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
    }

    private function bearer(string $accessToken): static
    {
        $this->forgetAuthentication();

        return $this->withToken($accessToken);
    }

    private function accessTokenId(string $jwt): string
    {
        $payload = explode('.', $jwt)[1];

        return json_decode(base64_decode(strtr($payload, '-_', '+/')), true)['jti'];
    }

    public function test_it_revokes_the_presented_access_token_and_its_refresh_token(): void
    {
        $user = User::factory()->create();
        $tokens = $this->signIn($user);
        $accessTokenId = $this->accessTokenId($tokens['access_token']);

        $this->bearer($tokens['access_token'])
            ->postJson('/api/user/revoke-token')
            ->assertNoContent();

        $this->assertTrue((bool) DB::table('oauth_access_tokens')->where('id', $accessTokenId)->value('revoked'));
        $this->assertSame(
            0,
            DB::table('oauth_refresh_tokens')->where('access_token_id', $accessTokenId)->where('revoked', false)->count()
        );

        $this->bearer($tokens['access_token'])
            ->getJson('/api/userinfo')
            ->assertUnauthorized();
    }

    public function test_the_refresh_token_can_no_longer_be_exchanged(): void
    {
        $user = User::factory()->create();
        $tokens = $this->signIn($user);

        $this->bearer($tokens['access_token'])
            ->postJson('/api/user/revoke-token')
            ->assertNoContent();

        $this->forgetAuthentication();

        $response = $this->withoutToken()->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokens['refresh_token'],
            'client_id' => $this->client->getKey(),
        ]);

        $this->assertFalse($response->isSuccessful(), 'A revoked refresh token minted a new access token.');
        $this->assertSame('invalid_grant', $response->json('error'));
    }

    public function test_the_users_other_tokens_keep_working(): void
    {
        $user = User::factory()->create();
        $thisDevice = $this->signIn($user);
        $otherDevice = $this->signIn($user);

        $this->bearer($thisDevice['access_token'])
            ->postJson('/api/user/revoke-token')
            ->assertNoContent();

        $this->bearer($otherDevice['access_token'])
            ->getJson('/api/userinfo')
            ->assertOk()
            ->assertJson(['id' => $user->id]);

        $this->forgetAuthentication();

        $this->withoutToken()->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $otherDevice['refresh_token'],
            'client_id' => $this->client->getKey(),
        ])->assertOk();
    }

    public function test_an_unauthenticated_request_gets_401(): void
    {
        $this->postJson('/api/user/revoke-token')->assertUnauthorized();
    }

    public function test_an_invalid_token_gets_401(): void
    {
        $this->bearer('not-a-token')
            ->postJson('/api/user/revoke-token')
            ->assertUnauthorized();
    }
}
