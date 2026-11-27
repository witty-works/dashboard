<?php

namespace Tests\Feature;

use App\Jobs\DeletePersonFromPosthog;
use App\Jobs\SyncToPosthog;
use App\Livewire\Profile\DeleteAccountForm;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Deleting an account deletes the person in PostHog, with their events and
 * recordings. PostHog cannot delete a group, so a deleted team's group keeps
 * its key but loses its name and owner.
 */
class PosthogDeletionTest extends TestCase
{
    use RefreshDatabase;

    // phpunit.xml's unreachable host, so the PostHog SDK sends nothing out.
    private const HOST = 'http://127.0.0.1:9';

    private const BULK_DELETE = self::HOST . '/api/projects/4711/persons/bulk_delete/';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'posthog.enabled' => true,
            'posthog.api_key' => 'phc_test',
            'posthog.host' => self::HOST,
            'posthog.project_id' => '4711',
            'posthog.personal_api_key' => 'phx_test',
            'posthog.dashboard_user_id_override' => null,
            'posthog.dashboard_team_id_override' => null,
        ]);
    }

    private function user(): User
    {
        $user = User::factory()->withPersonalTeam()->create(['password' => Hash::make('secret-password')]);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function deleteAccount(User $user)
    {
        $this->actingAs($user);

        return Livewire::test(DeleteAccountForm::class)
            ->set('password', 'secret-password')
            ->call('deleteUser');
    }

    private function bulkDeletes()
    {
        return Http::recorded(fn (Request $request) => $request->url() === self::BULK_DELETE);
    }

    public function test_deleting_an_account_deletes_the_person_with_their_events_and_recordings(): void
    {
        Http::fake(['127.0.0.1:9/*' => Http::response(['persons_queued_for_deletion' => 1], 202)]);
        $user = $this->user();
        $distinctId = 'dashboard-user:' . $user->id;

        $this->deleteAccount($user);

        $this->assertNull(User::find($user->id));
        $this->assertCount(1, $this->bulkDeletes());
        Http::assertSent(fn (Request $request) => $request->url() === self::BULK_DELETE
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer phx_test')
            && $request->data() === [
                'distinct_ids' => [$distinctId],
                'delete_events' => true,
                'delete_recordings' => true,
            ]);
    }

    public function test_nothing_is_deleted_in_posthog_when_the_account_deletion_is_refused(): void
    {
        Http::fake(['127.0.0.1:9/*' => Http::response([], 202)]);
        $owner = $this->user();
        $member = User::factory()->withPersonalTeam()->create();
        $owner->currentTeam->users()->attach($member, ['role' => 'user']);

        $this->deleteAccount($owner)->assertHasErrors('team');

        $this->assertNotNull(User::find($owner->id));
        $this->assertCount(0, $this->bulkDeletes());
    }

    public function test_a_posthog_outage_does_not_break_the_deletion_and_is_logged(): void
    {
        Http::fake(['127.0.0.1:9/*' => Http::response('unavailable', 503)]);
        Log::spy();
        $user = $this->user();
        $distinctId = 'dashboard-user:' . $user->id;

        $this->deleteAccount($user)->assertHasNoErrors();

        $this->assertNull(User::find($user->id));
        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message) => str_contains($message, $distinctId))
            ->once();
    }

    public function test_without_a_personal_api_key_the_person_to_delete_by_hand_is_logged(): void
    {
        config(['posthog.personal_api_key' => null]);
        Http::fake();
        Log::spy();
        $user = $this->user();
        $distinctId = 'dashboard-user:' . $user->id;

        $this->deleteAccount($user);

        $this->assertNull(User::find($user->id));
        Http::assertNothingSent();
        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message) => str_contains($message, $distinctId) && str_contains($message, 'POSTHOG_PERSONAL_API_KEY'))
            ->once();
    }

    public function test_nothing_is_sent_when_posthog_is_disabled(): void
    {
        config(['posthog.enabled' => false]);
        Http::fake();
        $user = $this->user();

        $this->deleteAccount($user);

        $this->assertNull(User::find($user->id));
        Http::assertNothingSent();
    }

    public function test_a_person_sent_to_posthog_before_it_was_switched_off_is_logged_for_deletion_by_hand(): void
    {
        config(['posthog.enabled' => false]);
        Http::fake();
        Log::spy();
        $user = $this->user();
        $user->forceFill(['posthog_last_sync' => now()->subMonth()])->saveQuietly();
        $distinctId = 'dashboard-user:' . $user->id;

        $this->deleteAccount($user);

        Http::assertNothingSent();
        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message) => str_contains($message, $distinctId) && str_contains($message, 'POSTHOG_ENABLED'))
            ->once();
    }

    public function test_a_person_never_sent_to_posthog_needs_no_deletion_when_it_is_off(): void
    {
        config(['posthog.enabled' => false]);
        Http::fake();
        Log::spy();
        $user = $this->user();
        $user->forceFill(['posthog_last_sync' => null])->saveQuietly();

        $this->deleteAccount($user);

        Http::assertNothingSent();
        Log::shouldNotHaveReceived('error');
    }

    public function test_a_deleted_teams_group_loses_its_name_and_owner(): void
    {
        $team = $this->user()->currentTeam;
        $job = new SyncToPosthog($team, false, true);
        DB::table('teams')->where('id', $team->id)->delete();

        $this->assertSame([
            'is_deleted' => true,
            'name' => null,
            'owner' => null,
            'impersonate_url' => null,
            'users' => 0,
        ], $job->handle());
    }

    public function test_nothing_is_deleted_in_posthog_when_the_deletion_is_rolled_back(): void
    {
        Http::fake(['127.0.0.1:9/*' => Http::response([], 202)]);
        $user = $this->user();

        try {
            DB::transaction(function () use ($user) {
                $user->delete();
                throw new \RuntimeException('rolled back');
            });
        } catch (\RuntimeException) {
        }

        $this->assertNotNull(User::find($user->id));
        $this->assertCount(0, $this->bulkDeletes());
    }

    public function test_a_refused_key_is_not_retried_and_is_logged(): void
    {
        Http::fake(['127.0.0.1:9/*' => Http::response(['detail' => 'missing scope'], 403)]);
        Log::spy();
        $user = $this->user();
        $job = new DeletePersonFromPosthog($user);
        $job->withFakeQueueInteractions();

        $job->handle();

        $job->assertNotReleased();
        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message) => str_contains($message, 'dashboard-user:' . $user->id))
            ->once();
    }

    public function test_the_person_to_delete_by_hand_is_logged_when_the_job_fails_for_good(): void
    {
        Log::spy();
        $user = $this->user();

        (new DeletePersonFromPosthog($user))->failed(new \RuntimeException('worker timed out'));

        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message) => str_contains($message, 'dashboard-user:' . $user->id))
            ->once();
    }

    public function test_posthog_syncs_queued_during_a_deletion_wait_for_it_to_be_saved(): void
    {
        // A sync run before the commit still sees the user and would send
        // their properties to PostHog after the person is deleted.
        $user = $this->user();
        $user->forceFill(['posthog_last_sync' => null])->saveQuietly();

        DB::transaction(function () use ($user) {
            dispatch(new SyncToPosthog($user, true));
            $this->assertNull($user->fresh()->posthog_last_sync);
        });

        $this->assertNotNull($user->fresh()->posthog_last_sync);
    }

    public function test_with_the_user_id_override_set_no_person_is_deleted(): void
    {
        // Every user then reports as this one person, so deleting it would
        // delete someone else's data.
        config(['posthog.dashboard_user_id_override' => 'dashboard-user:17']);
        Http::fake();
        $user = $this->user();

        $this->deleteAccount($user);

        $this->assertNull(User::find($user->id));
        Http::assertNothingSent();
    }

    public function test_with_the_team_id_override_set_the_shared_group_is_left_alone(): void
    {
        config(['posthog.dashboard_team_id_override' => 'dashboard-team:9']);
        $team = $this->user()->currentTeam;
        $job = new SyncToPosthog($team, false, true);
        DB::table('teams')->where('id', $team->id)->delete();

        $this->assertTrue($job->handle());
    }

    public function test_a_sync_queued_for_a_team_deleted_meanwhile_still_finishes(): void
    {
        $team = $this->user()->currentTeam;
        $job = new SyncToPosthog($team);
        DB::table('teams')->where('id', $team->id)->delete();

        $this->assertTrue($job->handle());
    }
}
