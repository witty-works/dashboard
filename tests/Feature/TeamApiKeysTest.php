<?php

namespace Tests\Feature;

use App\Livewire\Teams\ApiKeys;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Team owners and admins see their team's keys (whose, name, dates, never the
 * key) and can revoke them. Every action checks the permission itself, since
 * a Livewire action can be called without its button.
 */
class TeamApiKeysTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config([
            'app.nlp_api_endpoint.urls' => ['https://nlp.example'],
            'app.nlp_api_endpoint.sync_rules' => true,
            'posthog.dashboard_team_id_override' => null,
        ]);
    }

    private function owner(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function fakeTeamKeys(Team $team): void
    {
        Http::fake(function (Request $request) use ($team) {
            if ($request->method() === 'GET') {
                return str_contains($request->url(), urlencode($team->posthogId()))
                    ? Http::response([[
                        'id' => 'k1', 'email' => 'member@acme.example', 'team_id' => $team->posthogId(), 'name' => 'Editor',
                        'source' => 'dashboard', 'created_at' => '2026-11-26T10:00:00Z', 'expires_at' => null,
                    ]])
                    : Http::response([]);
            }

            return Http::response(null, 204);
        });
    }

    public function test_admin_sees_team_keys_without_secret(): void
    {
        $owner = $this->owner();
        $this->fakeTeamKeys($owner->currentTeam);

        $this->actingAs($owner)->get('/en/team/show')
            ->assertOk()
            ->assertSee(__('content.api_keys_team_title'))
            ->assertSee('member@acme.example')
            ->assertSee('Editor');
    }

    public function test_member_does_not_see_section(): void
    {
        $owner = $this->owner();
        $member = User::factory()->create();
        $owner->currentTeam->users()->attach($member, ['role' => 'user']);
        $member->switchTeam($owner->currentTeam);
        $this->fakeTeamKeys($owner->currentTeam);

        // Members are sent on to their profile, where the team's keys are not shown either.
        $this->actingAs($member->refresh())->followingRedirects()->get('/en/team/show')
            ->assertOk()
            ->assertDontSee(__('content.api_keys_team_title'));
    }

    public function test_member_cannot_call_revoke_action(): void
    {
        $owner = $this->owner();
        $member = User::factory()->create();
        $owner->currentTeam->users()->attach($member, ['role' => 'user']);
        $this->fakeTeamKeys($owner->currentTeam);
        $this->actingAs($member);

        Livewire::test(ApiKeys::class, ['team' => $owner->currentTeam])->assertForbidden();

        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }

    public function test_admin_revokes_team_key(): void
    {
        $owner = $this->owner();
        $this->fakeTeamKeys($owner->currentTeam);
        $this->actingAs($owner);

        Livewire::test(ApiKeys::class, ['team' => $owner->currentTeam])->call('revoke', 'k1');

        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://nlp.example/managed_keys/k1');
    }

    public function test_demoted_admin_no_longer_sees_the_list(): void
    {
        $owner = $this->owner();
        $admin = User::factory()->create();
        $owner->currentTeam->users()->attach($admin, ['role' => 'admin']);
        $this->fakeTeamKeys($owner->currentTeam);
        $this->actingAs($admin);
        $component = Livewire::test(ApiKeys::class, ['team' => $owner->currentTeam])->assertSee('member@acme.example');

        $owner->currentTeam->users()->updateExistingPivot($admin->id, ['role' => 'user']);

        $component->call('$refresh')->assertForbidden();
    }

    public function test_admin_cannot_revoke_key_of_another_team(): void
    {
        $owner = $this->owner();
        $this->fakeTeamKeys($this->owner()->currentTeam);
        $this->actingAs($owner);

        Livewire::test(ApiKeys::class, ['team' => $owner->currentTeam])->call('revoke', 'k1')->assertForbidden();

        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }
}
