<?php

namespace Tests\Feature;

use App\Livewire\Admin\Teams;
use App\Mail\TeamInvitation as TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A superadmin sets a team up, then invites its owner from /admin. When the
 * invitation is accepted the team becomes the invitee's and the superadmin
 * leaves it. At most one owner invitation per team, and only while a
 * superadmin owns the team.
 */
class OwnerInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Mail::fake();
        config(['auth.invite_only' => true]);
    }

    private function superadmin(): User
    {
        return tap(User::factory()->create())->assignRole(config('admin.role'));
    }

    private function page(User $actor)
    {
        return Livewire::actingAs($actor)->test(Teams::class);
    }

    private function setUpTeam(User $admin, string $name = 'Research'): Team
    {
        $this->page($admin)->set('newName', $name)->call('createTeam')->assertHasNoErrors();

        return Team::where('name', $name)->firstOrFail();
    }

    private function inviteOwner(User $admin, Team $team, string $email)
    {
        return $this->page($admin)->set("ownerEmail.{$team->id}", $email)->call('inviteOwner', $team->id);
    }

    private function member(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    public function test_accepting_an_owner_invitation_hands_the_team_over(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $invitee = User::factory()->create(['email' => 'owner@example.org']);
        $this->inviteOwner($admin, $team, 'owner@example.org')->assertHasNoErrors();
        $invitation = TeamInvitation::where('email', 'owner@example.org')->firstOrFail();
        $this->assertTrue((bool) $invitation->as_owner);

        $this->actingAs($invitee)->get(route('team-invitations.accept', ['invitation' => $invitation->id]))->assertRedirect();

        $team->refresh();
        $this->assertSame($invitee->id, $team->user_id);
        $this->assertFalse($team->hasUser($admin->fresh()));
        $this->assertNotSame($team->id, $admin->fresh()->current_team_id);
        $this->assertSame($team->id, $invitee->fresh()->current_team_id);
        $this->assertSame($team->id, $invitee->fresh()->license_team_id);
        $this->assertSame(0, TeamInvitation::count());
    }

    public function test_it_works_for_someone_already_in_another_team(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $invitee = $this->member();
        $otherTeam = $invitee->currentTeam;
        $this->inviteOwner($admin, $team, $invitee->email);
        $invitation = TeamInvitation::firstOrFail();

        $this->actingAs($invitee)->get(route('team-invitations.accept', ['invitation' => $invitation->id]));

        $this->assertSame($invitee->id, $team->fresh()->user_id);
        $this->assertTrue($invitee->fresh()->ownsTeam($otherTeam));
    }

    public function test_it_applies_after_verification_for_a_new_sign_up(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $this->inviteOwner($admin, $team, 'new@example.org');
        $invitation = TeamInvitation::firstOrFail();
        auth()->logout();

        $this->get(URL::signedRoute('team-invitations.accept-signed', ['invitation' => $invitation->id]))->assertRedirect(route('register'));
        $this->post(route('register'), [
            'name' => 'New Owner', 'email' => 'new@example.org',
            'password' => 'Str0ng-Passw0rd!', 'password_confirmation' => 'Str0ng-Passw0rd!', 'terms' => true,
        ])->assertSessionHasNoErrors();
        $user = User::where('email', 'new@example.org')->firstOrFail();
        $user->markEmailAsVerified();
        $this->actingAs($user)->get(route('root'));

        $this->assertSame($user->id, $team->fresh()->user_id);
        $this->assertSame(0, $user->ownedTeams()->where('personal_team', true)->count());
    }

    public function test_a_second_owner_invitation_for_the_team_is_refused(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $this->inviteOwner($admin, $team, 'first@example.org')->assertHasNoErrors();

        $this->inviteOwner($admin, $team, 'second@example.org')->assertHasErrors("ownerEmail.{$team->id}");

        $this->assertSame(['first@example.org'], TeamInvitation::pluck('email')->all());
    }

    public function test_an_owner_invitation_is_refused_for_a_team_an_ordinary_user_owns(): void
    {
        $admin = $this->superadmin();
        $team = $this->member()->currentTeam;

        $this->inviteOwner($admin, $team, 'taker@example.org')->assertHasErrors("ownerEmail.{$team->id}");

        $this->assertSame(0, TeamInvitation::count());
    }

    public function test_a_hand_over_is_refused_if_the_owner_lost_superadmin_meanwhile(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $invitee = User::factory()->create(['email' => 'owner@example.org']);
        $this->inviteOwner($admin, $team, 'owner@example.org');
        $invitation = TeamInvitation::firstOrFail();
        $admin->removeRole(config('admin.role'));

        $this->actingAs($invitee)->get(route('team-invitations.accept', ['invitation' => $invitation->id]))->assertForbidden();

        $this->assertSame($admin->id, $team->fresh()->user_id);
    }

    public function test_the_mail_names_the_invited_team_and_says_own(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin, 'Press office');
        // The inviter's current team is another one.
        $this->setUpTeam($admin, 'Elsewhere');

        $this->inviteOwner($admin, $team, 'owner@example.org');

        Mail::assertSent(TeamInvitationMail::class, function (TeamInvitationMail $mail) {
            $html = $mail->render();

            return str_contains($html, 'Press office')
                && ! str_contains($html, 'Elsewhere')
                && str_contains($html, e(__('content.invitation_as_owner', ['team' => 'Press office'])));
        });
    }

    public function test_the_waiting_section_lists_open_owner_invitations_with_their_date(): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $this->inviteOwner($admin, $team, 'owner@example.org');
        $sent = TeamInvitation::firstOrFail()->created_at;

        $this->page($admin)->assertSee('owner@example.org')->assertSee($sent->isoFormat('LL'));
    }

    public function test_resend_and_revoke_work_for_another_superadmins_team(): void
    {
        $first = $this->superadmin();
        $second = $this->superadmin();
        $team = $this->setUpTeam($first);
        $this->inviteOwner($first, $team, 'owner@example.org');
        $invitation = TeamInvitation::firstOrFail();

        $this->page($second)->call('resendInvitation', $invitation->id)->assertHasNoErrors();
        Mail::assertSent(TeamInvitationMail::class, 2);
        $this->assertSame($invitation->id, TeamInvitation::firstOrFail()->id);

        $this->page($second)->call('revokeInvitation', $invitation->id)->assertHasNoErrors();
        $this->assertSame(0, TeamInvitation::count());
    }

    #[DataProvider('actions')]
    public function test_an_ordinary_user_cannot_call_the_invitation_actions(string $action): void
    {
        $admin = $this->superadmin();
        $team = $this->setUpTeam($admin);
        $this->inviteOwner($admin, $team, 'owner@example.org');
        $invitation = TeamInvitation::firstOrFail();
        $component = $this->page($admin)->set("ownerEmail.{$team->id}", 'intruder@example.org');

        $this->actingAs($this->member());
        $component->call($action, $action === 'inviteOwner' ? $team->id : $invitation->id)->assertForbidden();

        $this->assertSame(['owner@example.org'], TeamInvitation::pluck('email')->all());
    }

    public static function actions(): array
    {
        return [['inviteOwner'], ['resendInvitation'], ['revokeInvitation']];
    }
}
