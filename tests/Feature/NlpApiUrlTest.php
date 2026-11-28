<?php

namespace Tests\Feature;

use App\Jobs\DeleteUserFromNlpApi;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * The NLP API's addresses work with or without a trailing slash, and a wrong
 * address fails loudly. The API answers its config writes and deletes with
 * 2xx, a delete of something already gone included, so a 404 means the
 * address is wrong: counted as success, nothing would ever reach the API.
 */
class NlpApiUrlTest extends TestCase
{
    use RefreshDatabase;

    private function endpoint(string $url): void
    {
        config(['app.nlp_api_endpoint' => [
            'urls' => [$url],
            'user' => 'dashboard',
            'password' => 'secret',
            'sync_rules' => true,
            'delay_per_count' => 0,
        ]]);
    }

    private function team()
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();

        return $user->ownedTeams()->firstOrFail();
    }

    public function test_a_trailing_slash_on_the_sync_endpoint_is_not_doubled(): void
    {
        $team = $this->team();
        $this->endpoint('https://nlp.example.org/');
        Http::fake(['nlp.example.org/*' => Http::response('', 204)]);

        (new SyncOrganizationToNlpApi($team))->handle();
        (new DeleteUserFromNlpApi('someone@example.org'))->handle();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://nlp.example.org/organization/configs');
        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://nlp.example.org/user/configs?'));
        Http::assertNotSent(fn (Request $request) => str_contains(substr($request->url(), strlen('https://')), '//'));
    }

    public function test_a_404_on_a_sync_fails_the_job(): void
    {
        $team = $this->team();
        $this->endpoint('https://nlp.example.org/wrong');
        Http::fake(['nlp.example.org/*' => Http::response(['detail' => 'Not Found'], 404)]);

        $this->expectException(RuntimeException::class);
        (new SyncOrganizationToNlpApi($team))->handle();
    }

    public function test_a_404_on_a_delete_fails_the_job(): void
    {
        $this->endpoint('https://nlp.example.org/wrong');
        Http::fake(['nlp.example.org/*' => Http::response(['detail' => 'Not Found'], 404)]);

        $this->expectException(RuntimeException::class);
        (new DeleteUserFromNlpApi('someone@example.org'))->handle();
    }
}
