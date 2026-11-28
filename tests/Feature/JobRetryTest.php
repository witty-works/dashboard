<?php

namespace Tests\Feature;

use App\Jobs\DeleteOrganizationFromNlpApi;
use App\Jobs\DeletePersonFromPosthog;
use App\Jobs\DeleteUserFromNlpApi;
use App\Jobs\RevokeApiKeysInNlpApi;
use App\Jobs\SendEventToPosthog;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncToPosthog;
use App\Jobs\SyncUserToNlpApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

/**
 * The NLP API and PostHog jobs keep trying through an outage of hours, with
 * a backoff that grows to an hour, and fail for good only after a day. With
 * 50 tries 10 seconds apart they used to give up after about 8 minutes, so a
 * short outage left a deleted account's settings and keys in the API.
 */
class JobRetryTest extends TestCase
{
    use RefreshDatabase;

    public static function jobs(): array
    {
        return [
            'SyncUserToNlpApi' => [SyncUserToNlpApi::class],
            'SyncOrganizationToNlpApi' => [SyncOrganizationToNlpApi::class],
            'DeleteUserFromNlpApi' => [DeleteUserFromNlpApi::class],
            'DeleteOrganizationFromNlpApi' => [DeleteOrganizationFromNlpApi::class],
            'RevokeApiKeysInNlpApi' => [RevokeApiKeysInNlpApi::class],
            'SyncToPosthog' => [SyncToPosthog::class],
            'SendEventToPosthog' => [SendEventToPosthog::class],
        ];
    }

    #[DataProvider('jobs')]
    public function test_a_job_keeps_trying_for_a_day_with_a_growing_backoff(string $class): void
    {
        $job = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        $this->assertEqualsWithDelta(now()->addDay()->timestamp, $job->retryUntil()->getTimestamp(), 5);
        $backoff = $job->backoff();
        $this->assertSame(60, $backoff[0]);
        $this->assertSame(3600, end($backoff));
        $sorted = $backoff;
        sort($sorted);
        $this->assertSame($sorted, $backoff, 'each wait is at least as long as the last');
        $this->assertSame($backoff, array_values(array_unique($backoff)), 'and no wait repeats');
    }

    public function test_the_posthog_deletion_keeps_trying_for_about_a_day(): void
    {
        $job = (new ReflectionClass(DeletePersonFromPosthog::class))->newInstanceWithoutConstructor();

        $waits = $job->backoff;
        $total = 0;
        for ($try = 1; $try < $job->tries; $try++) {
            $total += $waits[min($try - 1, count($waits) - 1)];
        }

        $this->assertGreaterThanOrEqual(20 * 3600, $total);
        $this->assertLessThanOrEqual(30 * 3600, $total);
    }

    public function test_an_outage_puts_the_job_back_and_fails_it_only_after_a_day(): void
    {
        config([
            'queue.default' => 'database',
            'app.nlp_api_endpoint.urls' => ['http://nlp.test'],
            'app.nlp_api_endpoint.sync_rules' => true,
        ]);
        $user = User::factory()->withPersonalTeam()->create();
        DB::table('jobs')->delete();
        Http::fake(['nlp.test/*' => Http::response('down', 503)]);

        SyncUserToNlpApi::dispatch($user, 'high');
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--queue' => 'high'])->assertSuccessful();

        // Put back for a minute, not failed.
        $this->assertSame(0, DB::table('failed_jobs')->count());
        $job = DB::table('jobs')->where('queue', 'high')->first();
        $this->assertNotNull($job);
        $this->assertGreaterThanOrEqual(now()->addSeconds(55)->timestamp, $job->available_at);

        // Still down a day later: the job fails for good.
        $this->travel(25)->hours();
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--queue' => 'high'])->assertSuccessful();

        $this->assertSame(1, DB::table('failed_jobs')->count());
        $this->assertSame(0, DB::table('jobs')->where('queue', 'high')->count());
    }
}
