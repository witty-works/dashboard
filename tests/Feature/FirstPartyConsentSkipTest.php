<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * The consent skip is conditioned on both who is asking and what they ask for.
 *
 * The application defines no scopes today, so these tests declare their own with
 * Passport::tokensCan() — the point is to cover the behaviour that only appears
 * once scopes exist, which is exactly the case that cannot be observed by
 * exercising the app as it stands.
 */
class FirstPartyConsentSkipTest extends TestCase
{
    use RefreshDatabase;

    private function client(): OAuthClient
    {
        $client = new OAuthClient();

        $client->forceFill([
            'name' => 'Witty Browser Extension',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => ['https://example.test/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();

        return $client;
    }

    private function makeFirstParty(OAuthClient $client): void
    {
        config(['passport.first_party_clients' => [(string) $client->getKey()]]);
    }

    /**
     * @param  string[]  $ids
     * @return \Laravel\Passport\Scope[]
     */
    private function scopes(array $ids): array
    {
        Passport::tokensCan([
            'read' => 'Read your guidelines',
            'write' => 'Change your guidelines',
        ]);

        return Passport::scopesFor($ids);
    }

    public function test_a_first_party_client_skips_when_nothing_is_requested(): void
    {
        $client = $this->client();
        $this->makeFirstParty($client);

        $this->assertTrue($client->skipsAuthorization(User::factory()->create(), []));
    }

    public function test_a_third_party_client_never_skips(): void
    {
        $client = $this->client();
        config(['passport.first_party_clients' => []]);

        $this->assertFalse($client->skipsAuthorization(User::factory()->create(), []));
    }

    public function test_a_first_party_client_does_not_skip_an_unlisted_scope(): void
    {
        $client = $this->client();
        $this->makeFirstParty($client);
        config(['passport.first_party_scopes' => []]);

        $this->assertFalse(
            $client->skipsAuthorization(User::factory()->create(), $this->scopes(['read'])),
            'A first-party client was handed a scope that nobody authorised.'
        );
    }

    public function test_a_first_party_client_skips_a_listed_scope(): void
    {
        $client = $this->client();
        $this->makeFirstParty($client);
        config(['passport.first_party_scopes' => ['read']]);

        $this->assertTrue(
            $client->skipsAuthorization(User::factory()->create(), $this->scopes(['read']))
        );
    }

    public function test_one_unlisted_scope_is_enough_to_require_consent(): void
    {
        $client = $this->client();
        $this->makeFirstParty($client);
        config(['passport.first_party_scopes' => ['read']]);

        $this->assertFalse(
            $client->skipsAuthorization(User::factory()->create(), $this->scopes(['read', 'write'])),
            'A partially-authorised request skipped consent for the whole set.'
        );
    }

    public function test_the_consent_screen_is_actually_shown_for_an_unlisted_scope(): void
    {
        $client = $this->client();
        $this->makeFirstParty($client);
        config(['passport.first_party_scopes' => []]);

        Passport::tokensCan(['read' => 'Read your guidelines']);

        $response = $this->actingAs(User::factory()->create())
            ->withoutLocaleRedirects()
            ->get('/oauth/authorize?' . http_build_query([
                'client_id' => $client->getKey(),
                'redirect_uri' => 'https://example.test/callback',
                'response_type' => 'code',
                'state' => 'test-state',
                'scope' => 'read',
                'code_challenge' => str_repeat('a', 43),
                'code_challenge_method' => 'S256',
            ]));

        // Not a redirect straight back to the client: the user gets a say.
        $response->assertOk();
        $response->assertSee('Read your guidelines', escape: false);
    }
}
