<?php

namespace Tests\Feature;

use App\Jobs\DeleteUserFromNlpApi;
use App\Jobs\SyncToPosthog;
use App\Livewire\Profile\DeleteAccountForm;
use App\Livewire\Teams\TeamMemberManagerHelpHero;
use App\Models\ConnectedAccount;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\Kpi;
use App\Models\LanguageGuidelines;
use App\Models\Team;
use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\ConfirmAccountDeletion;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Users delete their own account from the profile page. Everything that is
 * theirs goes with it, here and on the NLP API. A team they own goes too, so
 * an owner whose team has other members hands it over first, to an admin.
 */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    // No Bus::fake() by default: phpunit.xml runs the queue inline, so the
    // jobs a deletion queues run, as they would in production.

    private function user(array $attributes = []): User
    {
        $user = User::factory()->withPersonalTeam()->create($attributes + ['password' => Hash::make('secret-password')]);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function member(Team $team, string $role): User
    {
        $user = $this->user();
        $team->users()->attach($user, ['role' => $role]);
        $user->switchTeam($team);

        return $user->refresh();
    }

    private function deleteForm(User $user)
    {
        $this->actingAs($user);

        return Livewire::test(DeleteAccountForm::class);
    }

    // --- The profile page ---

    public function test_the_profile_page_offers_account_deletion(): void
    {
        $this->actingAs($this->user())
            ->get('/en/user/profile')
            ->assertOk()
            ->assertSeeLivewire(DeleteAccountForm::class);
    }

    // --- Confirming ---

    public function test_a_wrong_password_deletes_nothing(): void
    {
        $user = $this->user();

        $this->deleteForm($user)
            ->set('password', 'wrong')
            ->call('deleteUser')
            ->assertHasErrors('password');

        $this->assertModelExists($user);
    }

    public function test_the_password_deletes_the_account_and_signs_out(): void
    {
        $user = $this->user();

        $this->deleteForm($user)
            ->set('password', 'secret-password')
            ->call('deleteUser')
            ->assertRedirect();

        $this->assertModelMissing($user);
        $this->assertGuest();
    }

    public function test_an_account_with_a_password_cannot_confirm_with_its_email_address(): void
    {
        $user = $this->user(['email' => 'pw@example.com']);

        $this->deleteForm($user)
            ->set('password', 'pw@example.com')
            ->call('deleteUser')
            ->assertHasErrors('password');

        $this->assertModelExists($user);
    }

    public function test_password_attempts_are_limited(): void
    {
        $user = $this->user();
        $form = $this->deleteForm($user);
        foreach (range(1, 5) as $attempt) {
            $form->set('password', 'wrong')->call('deleteUser');
        }

        $form->set('password', 'secret-password')->call('deleteUser')->assertHasErrors('password');

        $this->assertModelExists($user);
    }

    private function passwordless(): User
    {
        $user = $this->user(['email' => 'office@example.com']);
        $user->forceFill(['password' => null])->save();

        return $user->refresh();
    }

    public function test_an_account_without_a_password_is_sent_a_confirmation_link(): void
    {
        Mail::fake();
        $user = $this->passwordless();

        $this->deleteForm($user)->call('deleteUser')->assertHasNoErrors()->assertSet('confirmationSent', true);

        Mail::assertQueued(ConfirmAccountDeletion::class, fn ($mail) => $mail->hasTo('office@example.com'));
        $this->assertModelExists($user);
    }

    public function test_the_confirmation_link_is_sent_at_most_once_a_minute(): void
    {
        Mail::fake();
        $user = $this->passwordless();

        $form = $this->deleteForm($user)->call('deleteUser');
        $form->set('confirmationSent', false)->call('deleteUser')->assertHasErrors('password');

        Mail::assertQueuedCount(1);
    }

    public function test_no_confirmation_link_while_a_team_has_to_be_handed_over(): void
    {
        Mail::fake();
        $user = $this->passwordless();
        $this->member($user->currentTeam, 'user');

        $this->deleteForm($user)->call('deleteUser')->assertHasErrors('team');

        Mail::assertNothingQueued();
    }

    public function test_the_confirmation_link_works_without_being_signed_in(): void
    {
        $user = $this->passwordless();
        $url = URL::temporarySignedRoute('account.delete.confirm', now()->addMinutes(30), ['user' => $user->id]);

        $this->get($url)->assertOk();
        $this->post($url)->assertRedirect();

        $this->assertModelMissing($user);
    }

    public function test_the_confirmation_link_says_why_when_a_team_has_to_be_handed_over(): void
    {
        $user = $this->passwordless();
        $team = $user->currentTeam;
        $url = URL::temporarySignedRoute('account.delete.confirm', now()->addMinutes(30), ['user' => $user->id]);
        $this->member($team, 'user');

        $this->post($url)->assertStatus(422)->assertSee($team->name);

        $this->assertModelExists($user);
    }

    public function test_the_confirmation_link_asks_before_deleting(): void
    {
        $user = $this->passwordless();
        $url = URL::temporarySignedRoute('account.delete.confirm', now()->addMinutes(30), ['user' => $user->id]);

        $this->actingAs($user)->get($url)->assertOk()->assertSee('method="POST"', false);
        $this->assertModelExists($user);

        $this->actingAs($user)->post($url)->assertRedirect();
        $this->assertModelMissing($user);
        $this->assertGuest();
    }

    public function test_the_confirmation_link_works_only_for_its_user_and_only_signed(): void
    {
        $user = $this->passwordless();
        $other = $this->user();
        $url = URL::temporarySignedRoute('account.delete.confirm', now()->addMinutes(30), ['user' => $user->id]);

        $this->actingAs($other)->post($url)->assertForbidden();
        $this->actingAs($user)->post(route('account.delete.confirm', ['user' => $user->id]))->assertForbidden();
        $this->post(URL::temporarySignedRoute('account.delete.confirm', now()->subMinute(), ['user' => $user->id]))->assertForbidden();

        $this->assertModelExists($user);
        $this->assertModelExists($other);
    }

    public function test_deleting_works_with_the_real_queue_and_a_remember_token(): void
    {
        $user = $this->user();
        $user->forceFill(['remember_token' => 'remembered'])->save();

        $this->deleteForm($user->refresh())->set('password', 'secret-password')->call('deleteUser')->assertRedirect();

        $this->assertModelMissing($user);
        $this->assertGuest();
    }

    // --- What goes with the account ---

    public function test_the_users_data_goes_with_the_account(): void
    {
        $user = $this->user();
        $other = $this->user();
        foreach ([$user, $other] as $owner) {
            LanguageGuidelines::forceCreate(['user_id' => $owner->id]);
            FalsePositive::forceCreate(['user_id' => $owner->id, 'false_positive' => 'word']);
            TermReplacement::forceCreate(['user_id' => $owner->id, 'term' => 'term', 'replacement' => 'other', 'language_code' => 'en']);
            Domain::forceCreate(['user_id' => $owner->id, 'domain' => 'example.com']);
            Kpi::forceCreate(['user_id' => $owner->id, 'date' => now()->toDateString(), 'kpi' => 'checks', 'value' => 1]);
            Kpi::forceCreate(['team_id' => $owner->currentTeam->id, 'date' => now()->toDateString(), 'kpi' => 'checks', 'value' => 1]);
            $tokenId = 'token-' . $owner->id;
            DB::table('oauth_access_tokens')->insert(['id' => $tokenId, 'user_id' => $owner->id, 'client_id' => 1, 'revoked' => false]);
            DB::table('oauth_refresh_tokens')->insert(['id' => 'refresh-' . $owner->id, 'access_token_id' => $tokenId, 'revoked' => false]);
            ConnectedAccount::forceCreate(['user_id' => $owner->id, 'provider' => 'microsoft_office', 'provider_id' => 'sub-' . $owner->id]);
            DB::table('sessions')->insert(['id' => 'session-' . $owner->id, 'user_id' => $owner->id, 'payload' => '', 'last_activity' => time()]);
        }
        $team = $user->currentTeam;

        $this->deleteForm($user)->set('password', 'secret-password')->call('deleteUser');

        foreach (['language_guidelines', 'false_positives', 'term_replacements', 'domains', 'kpis', 'connected_accounts', 'sessions'] as $table) {
            $this->assertDatabaseMissing($table, ['user_id' => $user->id]);
            $this->assertDatabaseHas($table, ['user_id' => $other->id]);
        }
        $this->assertModelMissing($team);
        $this->assertDatabaseMissing('kpis', ['team_id' => $team->id]);
        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('oauth_refresh_tokens', ['access_token_id' => 'token-' . $user->id]);
        $this->assertDatabaseHas('oauth_refresh_tokens', ['access_token_id' => 'token-' . $other->id]);
    }

    public function test_a_deleted_team_is_no_longer_anyones_licence(): void
    {
        $owner = $this->user();
        $team = $owner->currentTeam;
        $former = $this->user();
        $former->forceFill(['license_team_id' => $team->id])->save();

        $this->deleteForm($owner)->set('password', 'secret-password')->call('deleteUser')->assertRedirect();

        $this->assertNull($former->fresh()->license_team_id);
        $this->actingAs($former->fresh())->get('/en/user/profile')->assertOk();
    }

    public function test_a_members_deletion_leaves_the_teams_guidelines(): void
    {
        $owner = $this->user();
        $team = $owner->currentTeam;
        $member = $this->member($team, 'admin');
        FalsePositive::forceCreate(['team_id' => $team->id, 'false_positive' => 'team-word']);

        $this->deleteForm($member)->set('password', 'secret-password')->call('deleteUser')->assertRedirect();

        $this->assertDatabaseHas('false_positives', ['team_id' => $team->id, 'false_positive' => 'team-word']);
    }

    public function test_the_nlp_api_is_told_to_delete_the_users_data(): void
    {
        Bus::fake();
        $user = $this->user();

        $this->deleteForm($user)->set('password', 'secret-password')->call('deleteUser');

        Bus::assertDispatched(DeleteUserFromNlpApi::class);
    }

    public function test_a_posthog_sync_queued_for_a_deleted_user_finishes_without_retrying(): void
    {
        config(['posthog.enabled' => true, 'posthog.api_key' => 'phc_test', 'posthog.host' => 'https://posthog.invalid']);
        $user = $this->user();
        $job = new SyncToPosthog($user);
        $user->delete();

        $this->assertTrue($job->handle());
    }

    // --- Teams ---

    public function test_an_owner_whose_team_has_members_must_hand_it_over_first(): void
    {
        $owner = $this->user();
        $team = $owner->currentTeam;
        $member = $this->member($team, 'user');

        $this->deleteForm($owner)
            ->set('password', 'secret-password')
            ->call('deleteUser')
            ->assertHasErrors('team');

        $this->assertModelExists($owner);
        $this->assertModelExists($team);
        $this->assertTrue($team->fresh()->hasUser($member));
    }

    public function test_a_member_leaves_their_teams_when_deleting_their_account(): void
    {
        $owner = $this->user();
        $team = $owner->currentTeam;
        $member = $this->member($team, 'admin');

        $this->deleteForm($member)->set('password', 'secret-password')->call('deleteUser')->assertRedirect();

        $this->assertModelMissing($member);
        $this->assertModelExists($team);
        $this->assertDatabaseMissing('team_user', ['user_id' => $member->id]);
    }

    public function test_after_handing_the_team_over_the_former_owner_can_delete_their_account(): void
    {
        $owner = $this->user();
        $team = $owner->currentTeam;
        $admin = $this->member($team, 'admin');
        $this->member($team, 'user');

        $this->actingAs($owner);
        Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team])
            ->call('confirmOwnershipTransfer', $admin->id)
            ->call('transferOwnership')
            ->assertOk();

        $this->deleteForm($owner->fresh())->set('password', 'secret-password')->call('deleteUser')->assertRedirect();

        $this->assertModelMissing($owner);
        $this->assertSame($admin->id, $team->fresh()->user_id);
    }

    // --- Open invitations ---

    public function test_an_owner_whose_team_has_open_invitations_must_resolve_them_first(): void
    {
        // A team waiting for its owner has an invitation but no members;
        // deleting the account would delete the team with the invitation.
        $owner = $this->user();
        $team = $owner->currentTeam;
        $team->teamInvitations()->create(['email' => 'future@example.org', 'role' => 'user']);

        $this->deleteForm($owner)->set('password', 'secret-password')->call('deleteUser')->assertHasErrors('team');

        $this->assertNotNull($owner->fresh());
        $this->assertNotNull($team->fresh());
        $this->assertSame(1, $team->teamInvitations()->count());
    }

    public function test_the_profile_page_names_teams_with_open_invitations(): void
    {
        $owner = $this->user();
        $owner->currentTeam->forceFill(['name' => 'Waiting team'])->save();
        $owner->currentTeam->teamInvitations()->create(['email' => 'future@example.org', 'role' => 'user']);

        $this->deleteForm($owner)->assertSee(__('content.delete_account_open_invitations', ['teams' => 'Waiting team']));
    }

    public function test_no_confirmation_mail_is_sent_while_invitations_are_open(): void
    {
        Mail::fake();
        $owner = $this->passwordless();
        $owner->currentTeam->teamInvitations()->create(['email' => 'future@example.org', 'role' => 'user']);

        $this->deleteForm($owner)->call('deleteUser')->assertHasErrors('team');

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }
}
