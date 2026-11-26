<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Services\NlpApiKeys;
use App\Services\NlpApiKeysUnavailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The dashboard's client for the NLP API's /managed_keys routes. */
class NlpApiKeysTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'secret-key-from-the-api-0123456789abcdefgh';

    protected function setUp(): void
    {
        parent::setUp();
        // Creating users queues syncs to the endpoints configured below.
        Bus::fake();
        config([
            'app.nlp_api_endpoint.urls' => ['', 'https://nlp.example', 'https://second.example'],
            'app.nlp_api_endpoint.user' => 'sync',
            'app.nlp_api_endpoint.password' => 'pw',
            'app.nlp_api_endpoint.sync_rules' => true,
            'posthog.dashboard_team_id_override' => null,
        ]);
    }

    private function details(array $overrides = []): array
    {
        return [
            'id' => 'k1', 'email' => 'jane@acme.example', 'team_id' => 'dashboard-team:1', 'name' => 'Editor',
            'source' => 'dashboard', 'created_at' => '2026-11-26T10:00:00Z', 'expires_at' => null, ...$overrides,
        ];
    }

    private function team(): array
    {
        $user = User::factory()->withPersonalTeam()->create(['email' => 'jane@acme.example']);

        return [$user, $user->ownedTeams()->first()];
    }

    public function test_enabled_needs_an_endpoint_and_sync(): void
    {
        $this->assertTrue(app(NlpApiKeys::class)->enabled());

        config(['app.nlp_api_endpoint.urls' => ['', null]]);
        $this->assertFalse(app(NlpApiKeys::class)->enabled());

        config(['app.nlp_api_endpoint.urls' => ['https://nlp.example'], 'app.nlp_api_endpoint.sync_rules' => false]);
        $this->assertFalse(app(NlpApiKeys::class)->enabled());
    }

    public function test_create_sends_person_team_and_expiry_to_the_first_endpoint(): void
    {
        [$user, $team] = $this->team();
        Http::fake(['nlp.example/managed_keys' => Http::response(['key' => self::KEY, 'details' => $this->details()], 201)]);

        $created = app(NlpApiKeys::class)->create($user, $team, 'Editor', Carbon::parse('2027-01-31 00:00:00', 'UTC'));

        $this->assertSame(self::KEY, $created['key']);
        $this->assertSame('k1', $created['details']['id']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example/managed_keys'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Basic ' . base64_encode('sync:pw'))
            && $request['email'] === 'jane@acme.example'
            && $request['team_id'] === $team->posthogId()
            && $request['name'] === 'Editor'
            && $request['source'] === 'dashboard'
            && $request['expires_at'] === '2027-01-31T00:00:00+00:00');
        Http::assertSentCount(1);
    }

    public function test_lists_for_user_and_team(): void
    {
        [$user, $team] = $this->team();
        Http::fake(['nlp.example/managed_keys*' => Http::response([$this->details()])]);

        $this->assertSame('k1', app(NlpApiKeys::class)->forUser($user)[0]['id']);
        $this->assertSame('k1', app(NlpApiKeys::class)->forTeam($team)[0]['id']);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example/managed_keys?email=jane%40acme.example');
        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example/managed_keys?team_id=' . urlencode($team->posthogId()));
    }

    public function test_replace_and_revoke(): void
    {
        Http::fake([
            'nlp.example/managed_keys/k1/replace' => Http::response(['key' => self::KEY, 'details' => $this->details(['id' => 'k2'])], 201),
            'nlp.example/managed_keys/k1' => Http::response(null, 204),
        ]);

        $this->assertSame('k2', app(NlpApiKeys::class)->replace('k1', 86400)['details']['id']);
        app(NlpApiKeys::class)->revoke('k1');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example/managed_keys/k1/replace'
            && $request['grace_seconds'] === 86400);
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://nlp.example/managed_keys/k1');
    }

    public function test_key_ids_are_url_encoded(): void
    {
        Http::fake(['*' => Http::response(null, 204)]);

        app(NlpApiKeys::class)->revoke('../user/configs');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example/managed_keys/..%2Fuser%2Fconfigs');
    }

    public function test_failure_does_not_leak_response_body(): void
    {
        [$user, $team] = $this->team();
        Http::fake(['*' => Http::response(['detail' => 'something with ' . self::KEY], 500)]);

        try {
            app(NlpApiKeys::class)->create($user, $team, 'Editor', null);
            $this->fail('expected NlpApiKeysUnavailable');
        } catch (NlpApiKeysUnavailable $error) {
            $this->assertStringNotContainsString(self::KEY, $error->getMessage());
            $this->assertStringContainsString('500', $error->getMessage());
        }
    }

    public function test_connection_errors_become_unavailable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));

        $this->expectException(NlpApiKeysUnavailable::class);
        app(NlpApiKeys::class)->revoke('k1');
    }

    public function test_refusal_message_from_api_is_kept_for_409(): void
    {
        [$user, $team] = $this->team();
        Http::fake(['*' => Http::response(['detail' => 'An email may have at most 20 keys'], 409)]);

        try {
            app(NlpApiKeys::class)->create($user, $team, 'Editor', null);
            $this->fail('expected NlpApiKeysUnavailable');
        } catch (NlpApiKeysUnavailable $error) {
            $this->assertSame(409, $error->status);
        }
    }
}
