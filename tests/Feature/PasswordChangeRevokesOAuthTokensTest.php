<?php

namespace Tests\Feature;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * A new password ends the OAuth sessions too. Someone who reset their password
 * after their account was taken over would otherwise leave the browser
 * extension and write page tokens working, refresh tokens for up to 30 days.
 */
class PasswordChangeRevokesOAuthTokensTest extends TestCase
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

    public function test_a_password_reset_revokes_the_tokens(): void
    {
        $user = User::factory()->create();
        [$accessId, $refreshId] = $this->issueTokenPair($user);
        // Its access token revoked some other way: the refresh token still works.
        [, $orphanRefreshId] = $this->issueTokenPair($user, accessRevoked: true);

        (new ResetUserPassword())->reset($user, [
            'password' => 'a-new-password-1234',
            'password_confirmation' => 'a-new-password-1234',
        ]);

        $this->assertTrue($this->revoked('oauth_access_tokens', $accessId));
        $this->assertTrue($this->revoked('oauth_refresh_tokens', $refreshId));
        $this->assertTrue($this->revoked('oauth_refresh_tokens', $orphanRefreshId));
    }

    public function test_a_password_update_revokes_the_tokens(): void
    {
        $user = User::factory()->create(['password' => Hash::make('the-old-password-1234')]);
        [$accessId, $refreshId] = $this->issueTokenPair($user);
        $this->actingAs($user);

        (new UpdateUserPassword())->update($user, [
            'current_password' => 'the-old-password-1234',
            'password' => 'a-new-password-1234',
            'password_confirmation' => 'a-new-password-1234',
        ]);

        $this->assertTrue($this->revoked('oauth_access_tokens', $accessId));
        $this->assertTrue($this->revoked('oauth_refresh_tokens', $refreshId));
    }

    public function test_another_users_tokens_are_left_alone(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        [$accessId, $refreshId] = $this->issueTokenPair($other);

        (new ResetUserPassword())->reset($user, [
            'password' => 'a-new-password-1234',
            'password_confirmation' => 'a-new-password-1234',
        ]);

        $this->assertFalse($this->revoked('oauth_access_tokens', $accessId));
        $this->assertFalse($this->revoked('oauth_refresh_tokens', $refreshId));
    }
}
