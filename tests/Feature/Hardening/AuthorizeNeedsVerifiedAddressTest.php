<?php

namespace Tests\Feature\Hardening;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The extension's sign-in hands out tokens only for a verified address: an
 * account can claim any address, and /api/userinfo reports it to the
 * extension. Guests still go to the login screen first.
 */
class AuthorizeNeedsVerifiedAddressTest extends TestCase
{
    use RefreshDatabase;

    private function authorizeUrl(): string
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
        config(['passport.first_party_clients' => [(string) $client->getKey()]]);

        return '/oauth/authorize?' . http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'https://example.test/callback',
            'response_type' => 'code',
            'state' => 'test-state',
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'S256',
        ]);
    }

    public function test_an_unverified_address_gets_no_code(): void
    {
        $url = $this->authorizeUrl();

        $response = $this->actingAs(User::factory()->unverified()->create())
            ->withoutLocaleRedirects()
            ->get($url);

        $response->assertRedirect();
        $this->assertStringNotContainsString('example.test/callback', $response->headers->get('Location'));
    }

    public function test_verifying_the_address_returns_to_the_sign_in_it_interrupted(): void
    {
        $url = $this->authorizeUrl();

        $this->actingAs(User::factory()->unverified()->create())
            ->withoutLocaleRedirects()
            ->get($url);

        $intended = (string) session('url.intended');
        $this->assertStringStartsWith(url('/oauth/authorize') . '?', $intended);
        parse_str(parse_url($url, PHP_URL_QUERY), $expected);
        parse_str(parse_url($intended, PHP_URL_QUERY), $actual);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_a_verified_address_gets_a_code(): void
    {
        $url = $this->authorizeUrl();

        $response = $this->actingAs(User::factory()->create())
            ->withoutLocaleRedirects()
            ->get($url);

        $response->assertRedirect();
        $this->assertStringStartsWith('https://example.test/callback?code=', $response->headers->get('Location'));
    }

    public function test_a_guest_is_sent_to_sign_in(): void
    {
        $response = $this->withoutLocaleRedirects()->get($this->authorizeUrl());

        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }
}
