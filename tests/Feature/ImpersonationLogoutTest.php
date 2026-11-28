<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * A superadmin who logs out while impersonating someone ends their own
 * session, not that person's extension. The Logout event carries the
 * impersonated user, so revoking on it signed them out of the browser
 * extension without them knowing why.
 */
class ImpersonationLogoutTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    private function tokenFor(User $user): string
    {
        $client = new OAuthClient();
        $client->forceFill([
            'name' => 'Test Client',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => ['https://example.test/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();

        $id = Str::random(80);
        DB::table('oauth_access_tokens')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'client_id' => $client->getKey(),
            'scopes' => '[]',
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        return $id;
    }

    public function test_logging_out_while_impersonating_keeps_the_users_tokens(): void
    {
        $admin = $this->verifiedSuperadmin(User::factory()->withPersonalTeam()->create());
        $user = User::factory()->withPersonalTeam()->create();
        $token = $this->tokenFor($user);

        $this->actingAs($admin)->get('/en/impersonate/take/' . $user->id)->assertRedirect();
        $this->assertSame($user->id, auth()->id());

        $this->post(route('logout'));

        $this->assertFalse((bool) DB::table('oauth_access_tokens')->where('id', $token)->value('revoked'));
    }
}
