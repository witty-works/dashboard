<?php

namespace Tests\Feature;

use App\Livewire\Teams\TeamMemberManagerHelpHero;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Who may change a team's membership. The team member manager is rendered for
 * admins on the team page, and for members without admin rights on their
 * profile page, where they can only leave. The view hides the other buttons
 * from those members, but a Livewire action can be called whether or not its
 * button is shown, so each action has to check the permission itself.
 */
class TeamMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
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

    private function manager(User $user, Team $team)
    {
        $this->actingAs($user);

        return Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team]);
    }

    // --- TeamPolicy ---

    public static function abilities(): array
    {
        // ability => [owner, admin, user, outsider]
        return [
            'view' => ['view', [true, true, true, false]],
            'update' => ['update', [true, true, false, false]],
            'guidelines' => ['guidelines', [true, true, false, false]],
            'addTeamMember' => ['addTeamMember', [true, true, false, false]],
            'updateTeamMember' => ['updateTeamMember', [true, true, false, false]],
            'removeTeamMember' => ['removeTeamMember', [true, true, false, false]],
            'delete' => ['delete', [true, false, false, false]],
        ];
    }

    #[DataProvider('abilities')]
    public function test_the_team_policy_by_role(string $ability, array $expected): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $users = [
            $owner,
            $this->member($team, 'admin'),
            $this->member($team, 'user'),
            $this->owner(),
        ];

        $actual = array_map(fn (User $user) => Gate::forUser($user)->check($ability, $team), $users);

        $this->assertSame($expected, $actual, 'owner, admin, user, outsider');
    }

    // --- Invitations ---

    public function test_a_member_without_admin_rights_cannot_invite(): void
    {
        $team = $this->owner()->currentTeam;

        $this->manager($this->member($team, 'user'), $team)
            ->set('addTeamMemberForm.email', 'invited@example.com')
            ->set('addTeamMemberForm.role', 'admin')
            ->call('addTeamMember')
            ->assertForbidden();

        $this->assertDatabaseMissing('team_invitations', ['email' => 'invited@example.com']);
    }

    public function test_a_member_without_admin_rights_cannot_cancel_an_invitation(): void
    {
        $owner = $this->owner();
        $team = $owner->currentTeam;
        $invitation = $team->teamInvitations()->create(['email' => 'invited@example.com', 'role' => 'user']);

        $this->manager($this->member($team, 'user'), $team)
            ->call('cancelTeamInvitation', $invitation->id)
            ->assertForbidden();

        $this->assertModelExists($invitation);
    }

    public function test_an_admin_can_invite_and_cancel_an_invitation(): void
    {
        $team = $this->owner()->currentTeam;
        $admin = $this->member($team, 'admin');

        $this->manager($admin, $team)
            ->set('addTeamMemberForm.email', 'invited@example.com')
            ->set('addTeamMemberForm.role', 'user')
            ->call('addTeamMember')
            ->assertOk();

        $invitation = TeamInvitation::where('email', 'invited@example.com')->firstOrFail();
        $this->assertSame($team->id, $invitation->team_id);

        $this->manager($admin, $team)
            ->call('cancelTeamInvitation', $invitation->id)
            ->assertOk();

        $this->assertModelMissing($invitation);
    }

    public function test_an_invitation_is_not_applied_to_an_unverified_account(): void
    {
        $team = $this->owner()->currentTeam;
        TeamInvitation::forceCreate(['team_id' => $team->id, 'email' => 'invited@example.com', 'role' => 'admin', 'accepted' => true]);
        $claimant = User::factory()->unverified()->withPersonalTeam()->create(['email' => 'invited@example.com']);

        $claimant->applyAcceptedInvitiations();

        $this->assertFalse($team->fresh()->hasUser($claimant));
    }

    public function test_an_accepted_invitation_is_applied_to_a_verified_account(): void
    {
        $team = $this->owner()->currentTeam;
        TeamInvitation::forceCreate(['team_id' => $team->id, 'email' => 'invited@example.com', 'role' => 'user', 'accepted' => true]);
        $invitee = User::factory()->withPersonalTeam()->create(['email' => 'invited@example.com']);

        $invitee->applyAcceptedInvitiations();

        $this->assertTrue($team->fresh()->hasUser($invitee));
        $this->assertSame('user', $invitee->teamRole($team)->key);
    }

    public function test_the_accept_link_does_not_add_an_unverified_account(): void
    {
        $team = $this->owner()->currentTeam;
        $invitation = $team->teamInvitations()->create(['email' => 'invited@example.com', 'role' => 'admin']);
        $claimant = User::factory()->unverified()->withPersonalTeam()->create(['email' => 'invited@example.com']);

        $this->actingAs($claimant)
            ->get("/en/team-invitations/{$invitation->id}/accept")
            ->assertRedirect(route('verification.notice'));

        $this->assertFalse($team->fresh()->hasUser($claimant));
        $this->assertTrue((bool) $invitation->fresh()->accepted, 'kept for after verification');
    }

    public function test_the_accept_link_adds_a_verified_invitee(): void
    {
        $team = $this->owner()->currentTeam;
        $invitation = $team->teamInvitations()->create(['email' => 'invited@example.com', 'role' => 'user']);
        $invitee = User::factory()->withPersonalTeam()->create(['email' => 'invited@example.com']);

        $this->actingAs($invitee)->get("/en/team-invitations/{$invitation->id}/accept")->assertRedirect();

        $this->assertTrue($team->fresh()->hasUser($invitee));
        $this->assertModelMissing($invitation);
    }

    public function test_an_accepted_invitation_is_applied_once_the_address_is_verified(): void
    {
        $team = $this->owner()->currentTeam;
        TeamInvitation::forceCreate(['team_id' => $team->id, 'email' => 'invited@example.com', 'role' => 'user', 'accepted' => true]);
        $invitee = User::factory()->unverified()->withPersonalTeam()->create(['email' => 'invited@example.com']);

        $invitee->applyAcceptedInvitiations();
        $this->assertFalse($team->fresh()->hasUser($invitee));

        $invitee->markEmailAsVerified();
        $invitee->applyAcceptedInvitiations();
        $this->assertTrue($team->fresh()->hasUser($invitee));
    }

    public function test_an_accepted_invitation_to_a_team_the_user_is_already_on_is_dropped(): void
    {
        $team = $this->owner()->currentTeam;
        $member = $this->member($team, 'user');
        $invitation = TeamInvitation::forceCreate(['team_id' => $team->id, 'email' => $member->email, 'role' => 'admin', 'accepted' => true]);

        $member->applyAcceptedInvitiations();

        $this->assertModelMissing($invitation);
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $member->id, 'role' => 'user']);
    }

    // --- Members ---

    public function test_a_member_without_admin_rights_cannot_change_a_role_or_remove_a_member(): void
    {
        $team = $this->owner()->currentTeam;
        $other = $this->member($team, 'user');
        $member = $this->member($team, 'user');

        $this->manager($member, $team)
            ->call('manageRole', $other->id)
            ->set('currentRole', 'admin')
            ->call('updateRole')
            ->assertForbidden();

        $this->manager($member, $team)
            ->set('teamMemberIdBeingRemoved', $other->id)
            ->call('removeTeamMember')
            ->assertForbidden();

        $this->assertSame('user', $other->fresh()->teamRole($team)->key);
        $this->assertTrue($team->fresh()->hasUser($other));
    }

    public function test_a_member_can_leave_the_team(): void
    {
        $team = $this->owner()->currentTeam;
        $member = $this->member($team, 'user');

        $this->manager($member, $team)->call('leaveTeam');

        $this->assertFalse($team->fresh()->hasUser($member));
    }

    public function test_the_client_cannot_point_the_manager_at_another_team(): void
    {
        $owner = $this->owner();
        $other = $this->owner()->currentTeam;

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $this->manager($owner, $owner->currentTeam)->set('team', $other->id);
    }
}
