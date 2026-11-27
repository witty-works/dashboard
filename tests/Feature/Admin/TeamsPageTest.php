<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Teams;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * Superadmins see every team, set its rate limit (LLM requests per minute),
 * create teams they own until an owner takes over, and delete teams that
 * are still theirs.
 */
class TeamsPageTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function user(array $attributes = []): User
    {
        $user = User::factory()->withPersonalTeam()->create($attributes);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function superadmin(): User
    {
        return $this->superadminWithTwoFactor($this->user());
    }

    private function page(User $actor)
    {
        $this->verifyTwoFactorSession($actor);

        return Livewire::actingAs($actor)->test(Teams::class);
    }

    public function test_ordinary_users_are_refused(): void
    {
        $user = $this->user();

        $this->actingAs($user)->withoutLocaleRedirects()->get('/admin/teams')->assertForbidden();
        Livewire::actingAs($user)->test(Teams::class)->assertForbidden();
    }

    #[DataProvider('actions')]
    public function test_an_ordinary_user_cannot_call_the_actions(string $action): void
    {
        $admin = $this->superadmin();
        $user = $this->user();
        $team = $admin->currentTeam;
        $component = $this->page($admin)->set('newName', 'Sneaky')->set("limits.{$team->id}", 5);

        $this->actingAs($user);
        $component->call($action, ...($action === 'createTeam' ? [] : [$team->id]))->assertForbidden();

        $this->assertNull(Team::where('name', 'Sneaky')->first());
        $this->assertNull($team->fresh()?->llm_requests_per_minute);
        $this->assertNotNull($team->fresh());
    }

    public static function actions(): array
    {
        return [['createTeam'], ['saveLimit'], ['deleteTeam'], ['configure']];
    }

    public function test_it_lists_every_team_with_owner_members_and_limit(): void
    {
        $admin = $this->superadmin();
        $owner = $this->user(['email' => 'owner@example.org']);
        $team = $owner->currentTeam;
        $team->forceFill(['name' => 'Research', 'llm_requests_per_minute' => 42])->save();
        $team->users()->attach($this->user(), ['role' => 'user']);

        $this->page($admin)
            ->assertSee('Research')
            ->assertSee('owner@example.org')
            ->assertSet("limits.{$team->id}", 42);
    }

    public function test_a_superadmin_creates_a_team_they_own_with_a_limit(): void
    {
        $admin = $this->superadmin();

        $this->page($admin)->set('newName', 'Press office')->set('newLimit', 30)->call('createTeam')->assertHasNoErrors();

        $team = Team::where('name', 'Press office')->firstOrFail();
        $this->assertSame($admin->id, $team->user_id);
        $this->assertFalse((bool) $team->personal_team);
        $this->assertSame(30, $team->llm_requests_per_minute);
        $this->assertSame($team->id, $admin->fresh()->current_team_id);
    }

    public function test_any_superadmin_can_change_any_teams_limit(): void
    {
        $admin = $this->superadmin();
        $team = $this->user()->currentTeam;

        $this->page($admin)->set("limits.{$team->id}", 12)->call('saveLimit', $team->id)->assertHasNoErrors();
        $this->assertSame(12, $team->fresh()->llm_requests_per_minute);

        $this->page($admin)->set("limits.{$team->id}", '')->call('saveLimit', $team->id)->assertHasNoErrors();
        $this->assertNull($team->fresh()->llm_requests_per_minute);
    }

    #[DataProvider('invalidLimits')]
    public function test_the_limit_rejects_invalid_values(mixed $limit): void
    {
        $admin = $this->superadmin();
        $team = $this->user()->currentTeam;

        $this->page($admin)->set("limits.{$team->id}", $limit)->call('saveLimit', $team->id)->assertHasErrors();

        $this->assertNull($team->fresh()->llm_requests_per_minute);
    }

    public static function invalidLimits(): array
    {
        return [[0], [-1], [4294967296], ['ten'], [1.5]];
    }

    public function test_a_second_superadmin_can_delete_a_team_the_first_created(): void
    {
        $first = $this->superadmin();
        $second = $this->superadmin();
        $this->page($first)->set('newName', 'Stale')->call('createTeam');
        $team = Team::where('name', 'Stale')->firstOrFail();

        $this->page($second)->call('deleteTeam', $team->id)->assertHasNoErrors();

        $this->assertNull($team->fresh());
    }

    public function test_a_team_owned_by_an_ordinary_user_cannot_be_deleted_here(): void
    {
        $admin = $this->superadmin();
        $team = $this->user()->currentTeam;

        $this->page($admin)->call('deleteTeam', $team->id)->assertForbidden();

        $this->assertNotNull($team->fresh());
    }

    public function test_configure_switches_to_a_team_the_superadmin_owns(): void
    {
        $admin = $this->superadmin();
        $this->page($admin)->set('newName', 'Setup')->call('createTeam');
        $team = Team::where('name', 'Setup')->firstOrFail();
        $admin->switchTeam($admin->ownedTeams()->where('personal_team', true)->first());

        $this->page($admin)->call('configure', $team->id)->assertRedirect(route('teams.show'));

        $this->assertSame($team->id, $admin->fresh()->current_team_id);
    }

    public function test_admin_home_leads_to_the_teams_page(): void
    {
        $this->actingAs($this->verifiedSuperadmin($this->user()))->withoutLocaleRedirects()
            ->get('/admin')->assertRedirect('/admin/teams');
    }
}
