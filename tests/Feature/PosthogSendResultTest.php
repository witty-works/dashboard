<?php

namespace Tests\Feature;

use App\Jobs\SendEventToPosthog;
use App\Jobs\SyncToPosthog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvalidArgumentException;

/**
 * The PostHog client queues what it is given and reports success before
 * anything is sent. A job that PostHog never received must fail, so it is
 * retried through an outage (RetriesThroughOutages), and a sync must not be
 * recorded as sent, or the next one with the same data is skipped.
 */
class PosthogSendResultTest extends TestCase
{
    use RefreshDatabase;

    /** After the test's models exist: creating them syncs them, too. */
    private function unreachablePosthog(): void
    {
        config([
            'posthog.enabled' => true,
            'posthog.api_key' => 'phc_test',
            // A port nothing listens on: the connection is refused at once.
            'posthog.host' => 'http://127.0.0.1:9',
            'posthog.consumer' => 'lib_curl',
            'posthog.filename' => null,
            'posthog.debug' => false,
        ]);
    }

    private function fails(callable $run): void
    {
        try {
            $run();
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('The job reported success although PostHog could not be reached.');
    }

    public function test_an_event_postHog_never_received_fails_the_job(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->unreachablePosthog();
        $this->fails(fn () => (new SendEventToPosthog($user, 'test_event', []))->handle());
    }

    public function test_a_person_sync_postHog_never_received_fails_and_is_not_recorded(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $before = $user->refresh()->only(['posthog_last_sync', 'posthog_last_sync_data']);
        // A sync that went through would record a later time.
        $this->travel(5)->minutes();

        $this->unreachablePosthog();
        $this->fails(fn () => (new SyncToPosthog($user, true))->handle());

        $this->assertSame($before, $user->refresh()->only(['posthog_last_sync', 'posthog_last_sync_data']));
    }

    public function test_a_team_sync_postHog_never_received_fails_and_is_not_recorded(): void
    {
        $team = User::factory()->withPersonalTeam()->create()->ownedTeams()->firstOrFail();
        $before = $team->refresh()->only(['posthog_last_sync', 'posthog_last_sync_data']);
        $this->travel(5)->minutes();

        $this->unreachablePosthog();
        $this->fails(fn () => (new SyncToPosthog($team, true))->handle());

        $this->assertSame($before, $team->refresh()->only(['posthog_last_sync', 'posthog_last_sync_data']));
    }
}
