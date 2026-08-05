<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Logging out has to end the extension's session too, not just the browser's.
 *
 * The orphan case below is the one worth having: league/oauth2-server's refresh
 * grant never looks at the access token's `revoked` flag, so a refresh token
 * whose access token was revoked by some other path keeps minting new access
 * tokens for its full lifetime. Revoking only the live access tokens and their
 * refresh tokens leaves those behind, and nothing about the happy path reveals it.
 */
class LogoutRevokesOAuthTokensTest extends TestCase
{
    use RefreshDatabase;

    private OAuthClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new OAuthClient();
        $this->client->forceFill([
            'name' => 'Test Client',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => ['https://example.test/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();
    }

    /**
     * @return array{0: string, 1: string} access token id, refresh token id
     */
    private function issueTokenPair(User $user, bool $accessRevoked = false): array
    {
        $accessId = Str::random(80);
        $refreshId = Str::random(80);

        DB::table('oauth_access_tokens')->insert([
            'id' => $accessId,
            'user_id' => $user->id,
            'client_id' => $this->client->getKey(),
            'scopes' => '[]',
            'revoked' => $accessRevoked,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        DB::table('oauth_refresh_tokens')->insert([
            'id' => $refreshId,
            'access_token_id' => $accessId,
            'revoked' => false,
            'expires_at' => now()->addDays(30),
        ]);

        return [$accessId, $refreshId];
    }

    private function revoked(string $table, string $id): bool
    {
        return (bool) DB::table($table)->where('id', $id)->value('revoked');
    }

    public function test_logout_revokes_live_tokens(): void
    {
        $user = User::factory()->create();
        [$accessId, $refreshId] = $this->issueTokenPair($user);

        $this->actingAs($user)->withoutLocaleRedirects()->post('/logout')->assertRedirect();

        $this->assertTrue($this->revoked('oauth_access_tokens', $accessId));
        $this->assertTrue($this->revoked('oauth_refresh_tokens', $refreshId));
    }

    public function test_logout_revokes_a_refresh_token_whose_access_token_was_already_revoked(): void
    {
        $user = User::factory()->create();
        [, $orphanRefreshId] = $this->issueTokenPair($user, accessRevoked: true);

        $this->actingAs($user)->withoutLocaleRedirects()->post('/logout')->assertRedirect();

        $this->assertTrue(
            $this->revoked('oauth_refresh_tokens', $orphanRefreshId),
            'A refresh token survived logout because its access token was already revoked.'
        );
    }

    public function test_it_leaves_other_users_tokens_alone(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        [$accessId, $refreshId] = $this->issueTokenPair($other);

        $this->actingAs($user)->withoutLocaleRedirects()->post('/logout')->assertRedirect();

        $this->assertFalse($this->revoked('oauth_access_tokens', $accessId));
        $this->assertFalse($this->revoked('oauth_refresh_tokens', $refreshId));
    }
}
