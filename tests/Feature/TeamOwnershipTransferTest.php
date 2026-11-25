<?php

namespace Tests\Feature;

use App\Livewire\Teams\TeamMemberManagerHelpHero;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A team's owner can hand the team over to one of its admins, for example
 * before deleting their account. The former owner stays on as an admin.
 */
class TeamOwnershipTransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function owner(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function member(Team $team, string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team->users()->attach($user, ['role' => $role]);
        $user->switchTeam($team);

        return $user->refresh();
    }

    private function transfer(User $actor, Team $team, int $to)
    {
        $this->actingAs($actor);

        return Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team])
            ->call('confirmOwnershipTransfer', $to)
            ->call('transferOwnership');
    }

    public function test_the_owner_hands_the_team_to_an_admin(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $admin = $this->member($team, 'admin');

        $this->transfer($owner, $team, $admin->id)->assertOk();

        $team->refresh();
        $this->assertSame($admin->id, $team->user_id);
        $this->assertFalse((bool) $team->personal_team, 'a handed-over team is nobody\'s personal team');
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $admin->id]);
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $owner->id, 'role' => 'admin']);
        $this->assertTrue($owner->fresh()->hasTeamPermission($team, 'edit_guidelines'));
    }

    public function test_an_admin_who_is_not_the_owner_cannot_hand_the_team_over(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $admin = $this->member($team, 'admin');
        $other = $this->member($team, 'admin');

        $this->transfer($admin, $team, $other->id)->assertForbidden();

        $this->assertSame($owner->id, $team->fresh()->user_id);
    }

    public function test_the_team_goes_only_to_one_of_its_admins(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $member = $this->member($team, 'user');
        $outsider = $this->owner();

        $this->transfer($owner, $team, $member->id)->assertForbidden();
        $this->transfer($owner, $team, $outsider->id)->assertForbidden();

        $this->assertSame($owner->id, $team->fresh()->user_id);
    }

    public function test_the_owner_cannot_hand_the_team_to_themselves(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;

        $this->transfer($owner, $team, $owner->id)->assertForbidden();

        $this->assertSame($owner->id, $team->fresh()->user_id);
    }

    public function test_a_member_cannot_hand_the_team_over_from_the_profile_page(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $member = $this->member($team, 'user');
        $admin = $this->member($team, 'admin');

        $this->transfer($member, $team, $admin->id)->assertForbidden();

        $this->assertSame($owner->id, $team->fresh()->user_id);
    }
}
