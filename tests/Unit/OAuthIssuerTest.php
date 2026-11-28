<?php

namespace Tests\Unit;

use App\Auth\OAuthIssuer;
use Tests\TestCase;

/**
 * The issuer string is compared byte-for-byte by the NLP API (PyJWT checks `iss`
 * with plain string equality), so the trailing slash is not cosmetic: APP_URL is
 * written with one in .env.example and without one in the local .env.
 */
class OAuthIssuerTest extends TestCase
{
    public function test_it_uses_the_application_url(): void
    {
        config(['app.url' => 'https://dashboard.example.test']);

        $this->assertSame('https://dashboard.example.test', (new OAuthIssuer())->value());
    }

    public function test_it_strips_a_trailing_slash(): void
    {
        config(['app.url' => 'https://dashboard.example.test/']);

        $this->assertSame('https://dashboard.example.test', (new OAuthIssuer())->value());
    }

    public function test_it_does_not_mangle_a_path(): void
    {
        config(['app.url' => 'https://example.test/dashboard/']);

        $this->assertSame('https://example.test/dashboard', (new OAuthIssuer())->value());
    }
}
