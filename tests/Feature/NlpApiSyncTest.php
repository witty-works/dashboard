<?php

namespace Tests\Feature;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * What the NLP API learns about people and teams: every team a person is in,
 * which a key for a team needs, and each team's LLM limit. Membership changes
 * have to reach it, or a key outlives its person's membership.
 */
class NlpApiSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Bus::fake();
        // A development override gives every team the same id.
        config(['posthog.dashboard_team_id_override' => null, 'posthog.dashboard_user_id_override' => null]);
    }

    private function owner(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function syncedTeams(User $user, ?int $without = null): array
    {
        $data = (new SyncUserToNlpApi($user, 'high', $without))->getData($user->refresh());
        sort($data['teams']);

        return $data['teams'];
    }

    private function syncedFor(User $user): callable
    {
        return fn (SyncUserToNlpApi $job) => $job->userId() === $user->id;
    }

    public function test_user_sync_lists_all_teams(): void
    {
        $owner = $this->owner();
        $other = $this->owner();
        $member = $this->owner();
        $owner->currentTeam->users()->attach($member, ['role' => 'user']);
        $other->currentTeam->users()->attach($member, ['role' => 'admin']);

        $expected = [$owner->currentTeam->posthogId(), $other->currentTeam->posthogId(), $member->currentTeam->posthogId()];
        sort($expected);

        $this->assertSame($expected, $this->syncedTeams($member));
    }

    public function test_team_sync_sends_rate_limit(): void
    {
        $team = $this->owner()->currentTeam;
        $team->forceFill(['llm_requests_per_minute' => 30])->save();

        $data = (new SyncOrganizationToNlpApi($team))->getData($team->refresh());

        $this->assertSame(30, $data['llm_requests_per_minute']);
    }

    public function test_saving_limit_syncs_team(): void
    {
        $team = $this->owner()->currentTeam;

        $team->forceFill(['llm_requests_per_minute' => 30])->save();

        Bus::assertDispatched(SyncOrganizationToNlpApi::class);
    }

    public function test_adding_member_syncs_user(): void
    {
        $owner = $this->owner();
        $member = $this->owner();

        app(AddTeamMember::class)->add($owner, $owner->currentTeam, $member->email, 'user');

        Bus::assertDispatched(SyncUserToNlpApi::class, $this->syncedFor($member));
    }

    public function test_removing_member_moves_licence_team_and_syncs(): void
    {
        $owner = $this->owner();
        $member = $this->owner();
        $team = $owner->currentTeam;
        $team->users()->attach($member, ['role' => 'user']);
        $member->forceFill(['license_team_id' => $team->id])->save();

        app(RemoveTeamMember::class)->remove($owner, $team, $member);

        $member->refresh();
        $this->assertSame($member->ownedTeams()->first()->id, $member->license_team_id);
        Bus::assertDispatched(SyncUserToNlpApi::class, $this->syncedFor($member));
        $this->assertNotContains($team->posthogId(), $this->syncedTeams($member));
    }

    public function test_removing_member_from_their_only_team_clears_licence_team(): void
    {
        $owner = $this->owner();
        $member = User::factory()->create();
        $team = $owner->currentTeam;
        $team->users()->attach($member, ['role' => 'user']);
        $member->forceFill(['license_team_id' => $team->id, 'current_team_id' => $team->id])->save();

        app(RemoveTeamMember::class)->remove($owner, $team, $member);

        $this->assertNull($member->refresh()->license_team_id);
        $this->assertSame([], $this->syncedTeams($member));
    }

    public function test_deleting_team_moves_members_licence_team(): void
    {
        $owner = $this->owner();
        $member = $this->owner();
        $team = Team::forceCreate(['user_id' => $owner->id, 'name' => 'Doomed', 'personal_team' => false]);
        $team->users()->attach($member, ['role' => 'user']);
        $member->forceFill(['license_team_id' => $team->id])->save();

        app(DeleteTeam::class)->delete($team);

        $member->refresh();
        $this->assertSame($member->ownedTeams()->first()->id, $member->license_team_id);
        // The sync was queued while the team still existed; it must not list it.
        Bus::assertDispatched(
            SyncUserToNlpApi::class,
            fn (SyncUserToNlpApi $job) => $job->userId() === $member->id
                && ! in_array($team->posthogId(), $job->getData($member)['teams'], true),
        );
    }

    public function test_a_sync_queued_before_the_deploy_still_runs(): void
    {
        $user = $this->owner();
        // As unserialised from a payload written before $withoutTeamId existed.
        $job = (new \ReflectionClass(SyncUserToNlpApi::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($job, 'id'))->setValue($job, $user->id);

        $this->assertSame([$user->currentTeam->posthogId()], $job->getData($user)['teams']);
    }
}
