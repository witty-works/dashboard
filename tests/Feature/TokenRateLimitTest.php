<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * POST /oauth/token is rate limited per IP; the write page's client has a
 * limit of its own, so a page reload in a shared office does not use up the
 * extension's. Both limits are deployment settings.
 */
class TokenRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const EXTENSION = 'extension-client';

    private const EDITOR = 'editor-client';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'passport.token_rate_limit' => 2,
            'passport.editor.token_rate_limit' => 3,
            'passport.editor.client_id' => self::EDITOR,
        ]);
        RateLimiter::clear('auth');
    }

    private function token(string $clientId): int
    {
        return $this->post('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'refresh_token' => 'not-a-token',
        ])->getStatusCode();
    }

    public function test_the_general_limit_applies_to_other_clients(): void
    {
        $this->assertNotSame(429, $this->token(self::EXTENSION));
        $this->assertNotSame(429, $this->token(self::EXTENSION));
        $this->assertSame(429, $this->token(self::EXTENSION));
    }

    public function test_the_editor_client_has_its_own_limit(): void
    {
        foreach (range(1, 3) as $request) {
            $this->assertNotSame(429, $this->token(self::EDITOR));
        }
        $this->assertSame(429, $this->token(self::EDITOR));
    }

    public function test_the_editor_limit_does_not_use_up_the_general_one(): void
    {
        foreach (range(1, 3) as $request) {
            $this->token(self::EDITOR);
        }
        $this->assertNotSame(429, $this->token(self::EXTENSION));
    }
}
