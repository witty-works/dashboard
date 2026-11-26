<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;
use Tests\Concerns\FakesOfficeTokens;
use Tests\TestCase;

/**
 * In invite-only mode nobody gets a personal team. A user without a team
 * sees a page with their pending invitations, but can still verify their
 * address, sign out and accept an invitation; a superadmin goes to /admin.
 */
class InviteOnlyTeamsTest extends TestCase
{
    use FakesOfficeTokens;
    use RefreshDatabase;

    private const CLIENT_ID = 'office-client-id';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config([
            'auth.invite_only' => true,
            'services.microsoft_office.client_id' => self::CLIENT_ID,
            'services.microsoft_office.redirect_uri' => 'https://office.example/done',
        ]);
    }

    private function teamWithOwner(string $name = 'Research'): Team
    {
        $owner = User::factory()->create();
        $team = Team::forceCreate(['user_id' => $owner->id, 'name' => $name, 'personal_team' => false]);
        $owner->switchTeam($team);

        return $team;
    }

    private function invite(Team $team, string $email): TeamInvitation
    {
        return $team->teamInvitations()->create(['email' => $email, 'role' => 'user']);
    }

    private function signUp(string $email): User
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => $email,
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ])->assertSessionHasNoErrors();

        return User::where('email', $email)->firstOrFail();
    }

    public function test_an_invitee_gets_no_personal_team(): void
    {
        $team = $this->teamWithOwner();
        $invitation = $this->invite($team, 'ada@example.com');

        $user = $this->signUp('ada@example.com');
        $this->assertSame(0, $user->ownedTeams()->count());

        // Accepting before verifying keeps the acceptance for later.
        $this->actingAs($user)->get(URL::signedRoute('team-invitations.accept-signed', ['invitation' => $invitation->id]));
        $user->markEmailAsVerified();
        $this->get(route('root'));

        $user->refresh();
        $this->assertSame(0, $user->ownedTeams()->count());
        $this->assertTrue($user->belongsToTeam($team));
        $this->assertSame($team->id, $user->current_team_id);
    }

    public function test_an_office_sign_up_gets_no_personal_team(): void
    {
        $this->invite($this->teamWithOwner(), 'ada@example.com');

        $this->post('/en/office-register', [
            'token' => $this->token(['email' => 'ada@example.com', 'xms_edov' => true, 'name' => 'Ada']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ]);

        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $this->assertSame(0, $user->ownedTeams()->count());
    }

    public function test_a_user_without_a_team_can_still_verify_sign_out_and_accept_an_invitation(): void
    {
        $team = $this->teamWithOwner();
        $invitation = $this->invite($team, 'ada@example.com');
        $user = $this->signUp('ada@example.com');

        $this->actingAs($user)->get(route('verification.notice'))->assertOk();
        $this->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]))
            ->assertRedirect();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $this->get(route('team-invitations.accept', ['invitation' => $invitation->id]))->assertRedirect(config('fortify.home'));
        $this->assertTrue($user->fresh()->belongsToTeam($team));

        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_a_user_without_a_team_sees_the_no_team_page_with_their_pending_invitations(): void
    {
        $team = $this->teamWithOwner('Research');
        $this->invite($team, 'ada@example.com');
        $user = $this->signUp('ada@example.com');
        $user->markEmailAsVerified();

        $this->actingAs($user)->get(route('root'))->assertRedirect(route('no-team'));
        $this->get(route('no-team'))->assertOk()->assertSee('Research');
    }

    public function test_a_user_removed_from_their_only_team_sees_the_no_team_page(): void
    {
        $team = $this->teamWithOwner();
        $user = User::factory()->create();
        $team->users()->attach($user, ['role' => 'user']);
        $user->switchTeam($team);

        app(RemovesTeamMembers::class)->remove($team->owner, $team, $user);

        $this->actingAs($user->fresh())->get(route('root'))->assertRedirect(route('no-team'));
        $this->assertNull($user->fresh()->current_team_id);
    }

    public function test_a_superadmin_without_a_team_lands_on_admin(): void
    {
        $this->artisan('superadmin:create', ['email' => 'admin@example.org', 'name' => 'Ada Admin']);
        $admin = User::where('email', 'admin@example.org')->firstOrFail();

        $this->actingAs($admin)->get(route('root'))->assertRedirect(route('admin.home'));
        $this->assertSame(0, $admin->fresh()->ownedTeams()->count());
    }

    public function test_ordinary_users_cannot_create_teams(): void
    {
        $team = $this->teamWithOwner();
        $user = $team->owner;

        try {
            app(CreatesTeams::class)->create($user, ['name' => 'Mine']);
            $this->fail('An ordinary user created a team.');
        } catch (AuthorizationException) {
        }

        Livewire::actingAs($user)->test(CreateTeamForm::class)
            ->set('state.name', 'Mine')->call('createTeam')->assertForbidden();
        $this->assertSame(1, Team::count());
    }

    public function test_superadmins_can_create_teams(): void
    {
        $team = $this->teamWithOwner();
        $team->owner->assignRole(config('admin.role'));

        app(CreatesTeams::class)->create($team->owner, ['name' => 'Another']);

        $this->assertSame(2, Team::count());
    }

    public function test_with_the_mode_off_a_new_user_still_gets_a_personal_team(): void
    {
        config(['auth.invite_only' => false]);

        $user = $this->signUp('stranger@example.com');

        $this->assertSame(1, $user->ownedTeams()->count());
    }

    public function test_a_user_without_a_team_can_still_answer_the_mailing_question(): void
    {
        $user = User::factory()->create(['has_consented_to_mailing' => null]);

        $response = $this->actingAs($user)->get(route('user.mailing_consent', ['consent' => 1]));

        $this->assertNotSame(route('no-team'), $response->headers->get('Location'));
    }
}
