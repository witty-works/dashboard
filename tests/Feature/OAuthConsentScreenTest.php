<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The consent screen is unreachable in normal operation — the browser extension
 * is the only client and it is first-party, so it skips the prompt. That is
 * exactly why it needs a test: without one, the first client that is *not* on
 * PASSPORT_FIRST_PARTY_CLIENTS would be the thing that discovers the view is
 * broken, in production.
 *
 * Passport 13 ships no views of its own, so this template is entirely ours.
 */
class OAuthConsentScreenTest extends TestCase
{
    use RefreshDatabase;

    private function client(array $attributes = []): OAuthClient
    {
        $client = new OAuthClient();

        $client->forceFill(array_merge([
            'name' => 'Third Party App',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => ['https://third-party.example.test/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ], $attributes))->save();

        return $client;
    }

    private function authorizeUrl(OAuthClient $client): string
    {
        return '/oauth/authorize?' . http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'https://third-party.example.test/callback',
            'response_type' => 'code',
            'state' => 'test-state',
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'S256',
        ]);
    }

    public function test_a_non_first_party_client_gets_a_rendered_consent_screen(): void
    {
        $client = $this->client();

        config(['passport.first_party_clients' => []]);

        $response = $this->actingAs(User::factory()->create())
            ->withoutLocaleRedirects()
            ->get($this->authorizeUrl($client));

        $response->assertOk();
        $response->assertSee('Third Party App', escape: false);
        // The approve and deny forms must both be present and carry the session
        // token the controllers check; a consent screen that cannot be submitted
        // is the same as no consent screen.
        $response->assertSee('name="auth_token"', escape: false);
        $response->assertSee(route('passport.authorizations.approve'), escape: false);
        $response->assertSee(route('passport.authorizations.deny'), escape: false);
    }

    public function test_a_first_party_client_skips_the_consent_screen(): void
    {
        $client = $this->client(['name' => 'Our Extension']);

        config(['passport.first_party_clients' => [(string) $client->getKey()]]);

        $response = $this->actingAs(User::factory()->create())
            ->withoutLocaleRedirects()
            ->get($this->authorizeUrl($client));

        // Straight back to the client with a code, no interstitial.
        $response->assertRedirectContains('https://third-party.example.test/callback');
        $response->assertRedirectContains('code=');
    }

    public function test_the_client_name_is_escaped(): void
    {
        $client = $this->client(['name' => '<script>alert(1)</script>']);

        config(['passport.first_party_clients' => []]);

        $response = $this->actingAs(User::factory()->create())
            ->withoutLocaleRedirects()
            ->get($this->authorizeUrl($client));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', escape: false);
    }
}
