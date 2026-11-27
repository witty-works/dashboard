<?php

namespace Tests\Feature;

use App\Jobs\DeleteUserFromNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Models\User;
use App\Services\NlpApiKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Error messages about the NLP API end up in Sentry, the logs and the
 * failed-job mail, so they name the request and its status, not a person:
 * no address from a query string, and no response body, which may echo one.
 */
class NlpApiErrorMessagesTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'jane@acme.example';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config([
            'app.nlp_api_endpoint.urls' => ['https://nlp.example'],
            'app.nlp_api_endpoint.user' => 'sync',
            'app.nlp_api_endpoint.password' => 'pw',
            'app.nlp_api_endpoint.sync_rules' => true,
        ]);
    }

    private function messageOf(callable $call): string
    {
        try {
            $call();
        } catch (\Throwable $error) {
            return $error->getMessage();
        }
        $this->fail('Expected an exception');
    }

    public function test_a_failed_deletion_names_neither_the_address_nor_the_answer(): void
    {
        Http::fake(['*' => Http::response(['detail' => 'No config for ' . self::EMAIL], 500)]);
        $user = User::factory()->create(['email' => self::EMAIL]);
        $job = new DeleteUserFromNlpApi($user);

        $message = $this->messageOf(fn () => $job->handle());

        $this->assertStringNotContainsString(self::EMAIL, $message);
        $this->assertStringNotContainsString('acme.example', $message);
        $this->assertStringContainsString('500', $message);
    }

    public function test_a_failed_sync_does_not_repeat_the_answer(): void
    {
        Http::fake(['*' => Http::response(['detail' => 'Invalid config for ' . self::EMAIL], 422)]);
        $user = User::factory()->withPersonalTeam()->create(['email' => self::EMAIL]);

        $message = $this->messageOf(fn () => (new SyncUserToNlpApi($user))->handle());

        $this->assertStringNotContainsString(self::EMAIL, $message);
        $this->assertStringContainsString('422', $message);
    }

    public function test_an_unreachable_api_is_reported_without_the_address(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $user = User::factory()->create(['email' => self::EMAIL]);

        $message = $this->messageOf(fn () => app(NlpApiKeys::class)->forUser($user));

        $this->assertStringNotContainsString(self::EMAIL, $message);
        $this->assertStringNotContainsString('acme.example', $message);
    }

    /**
     * A real refused connection, not Http::failedConnection(): Guzzle's
     * message ends with the full URL, query string included.
     */
    public function test_an_unreachable_api_fails_a_deletion_without_the_address(): void
    {
        config(['app.nlp_api_endpoint.urls' => ['http://127.0.0.1:9']]);
        Http::allowStrayRequests();
        $user = User::factory()->create(['email' => self::EMAIL]);
        $job = new DeleteUserFromNlpApi($user);

        $message = $this->messageOf(fn () => $job->handle());

        $this->assertStringNotContainsString('acme.example', $message);
        $this->assertStringContainsString('could not be reached', $message);
    }

    public function test_an_unreachable_api_fails_a_sync_without_the_address(): void
    {
        config(['app.nlp_api_endpoint.urls' => ['http://127.0.0.1:9']]);
        Http::allowStrayRequests();
        $user = User::factory()->withPersonalTeam()->create(['email' => self::EMAIL]);

        $message = $this->messageOf(fn () => (new SyncUserToNlpApi($user))->handle());

        $this->assertStringNotContainsString('acme.example', $message);
        $this->assertStringContainsString('could not be reached', $message);
    }
}
