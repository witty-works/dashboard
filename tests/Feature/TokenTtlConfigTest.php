<?php

namespace Tests\Feature;

use App\Providers\AuthServiceProvider;
use DateInterval;
use Illuminate\Support\Facades\Exceptions;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The TTLs are parsed in AuthServiceProvider::boot(), which runs on every
 * request, and DateInterval throws on a malformed string. An env typo must not
 * be able to take the whole application down.
 */
class TokenTtlConfigTest extends TestCase
{
    private function ttl(string $key, string $default): DateInterval
    {
        $method = new ReflectionMethod(AuthServiceProvider::class, 'ttl');
        $method->setAccessible(true);

        return $method->invoke(new AuthServiceProvider($this->app), $key, $default);
    }

    public function test_it_parses_a_valid_duration(): void
    {
        config(['passport.access_token_ttl' => 'PT2H']);

        $this->assertSame(2, $this->ttl('access_token_ttl', 'PT1H')->h);
    }

    public function test_a_malformed_duration_falls_back_instead_of_throwing(): void
    {
        Exceptions::fake();

        // "1H" is the plausible typo: valid-looking, missing the leading P.
        config(['passport.access_token_ttl' => '1H']);

        $interval = $this->ttl('access_token_ttl', 'PT1H');

        $this->assertSame(1, $interval->h);
        // Falling back silently would hide the mistake, so it is still reported.
        Exceptions::assertReported(\RuntimeException::class);
    }
}
