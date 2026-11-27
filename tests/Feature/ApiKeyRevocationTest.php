<?php

namespace Tests\Feature;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\HandOverInvitedTeam;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A key works only while its person is in its team: every way of leaving a
 * team, losing it or the account, or changing the address the API knows the
 * person by, revokes keys in the NLP API, on every endpoint.
 */
class ApiKeyRevocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'app.nlp_api_endpoint.urls' => ['https://nlp.example', 'https://second.example'],
            'app.nlp_api_endpoint.user' => 'sync',
            'app.nlp_api_endpoint.password' => 'pw',
            'app.nlp_api_endpoint.sync_rules' => true,
            'posthog.dashboard_team_id_override' => null,
            'posthog.dashboard_user_id_override' => null,
        ]);
        Http::fake(['*' => Http::response(['count' => 0])]);
    }

    private function owner(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function revocations(): array
    {
        return Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/managed_keys/revoke'))
            ->map(fn ($pair) => [parse_url($pair[0]->url(), PHP_URL_HOST), $pair[0]['email'] ?? null, $pair[0]['team_id'] ?? null])
            ->values()
            ->all();
    }

    public function test_removal_revokes_that_teams_keys(): void
    {
        $owner = $this->owner();
        $member = $this->owner();
        $team = $owner->currentTeam;
        $team->users()->attach($member, ['role' => 'user']);

        app(RemoveTeamMember::class)->remove($owner, $team, $member);

        $this->assertEqualsCanonicalizing([
            ['nlp.example', $member->email, $team->posthogId()],
            ['second.example', $member->email, $team->posthogId()],
        ], $this->revocations());
    }

    public function test_team_deletion_revokes_without_failing_jobs(): void
    {
        $owner = $this->owner();
        $member = $this->owner();
        $team = Team::forceCreate(['user_id' => $owner->id, 'name' => 'Doomed', 'personal_team' => false]);
        $team->users()->attach($member, ['role' => 'user']);

        app(DeleteTeam::class)->delete($team);

        $this->assertContains(['nlp.example', null, $team->posthogId()], $this->revocations());
        $this->assertContains(['second.example', null, $team->posthogId()], $this->revocations());
        $this->assertContains(['nlp.example', $member->email, $team->posthogId()], $this->revocations());
    }

    public function test_account_deletion_revokes_all_keys(): void
    {
        $user = $this->owner();
        $email = $user->email;

        app(DeleteUser::class)->delete($user);

        $this->assertContains(['nlp.example', $email, null], $this->revocations());
        $this->assertContains(['second.example', $email, null], $this->revocations());
    }

    public function test_email_change_revokes_keys_of_the_old_address(): void
    {
        $user = $this->owner();
        $old = $user->email;

        app(UpdateUserProfileInformation::class)->update($user, ['name' => $user->name, 'email' => 'new@acme.example']);

        $this->assertContains(['nlp.example', $old, null], $this->revocations());
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://nlp.example/user/configs?' . http_build_query(['email' => $old]));
    }

    public function test_any_email_change_revokes_once(): void
    {
        $user = $this->owner();
        $old = $user->email;

        // Not through the profile form: a sign-in provider or an admin tool.
        $user->forceFill(['email' => 'new@acme.example'])->save();
        app(UpdateUserProfileInformation::class)->update($user->refresh(), ['name' => $user->name, 'email' => 'newer@acme.example']);

        $this->assertSame([
            ['nlp.example', $old, null], ['second.example', $old, null],
            ['nlp.example', 'new@acme.example', null], ['second.example', 'new@acme.example', null],
        ], $this->revocations());
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://nlp.example/user/configs?' . http_build_query(['email' => $old]));
    }

    public function test_email_case_change_keeps_keys(): void
    {
        $user = $this->owner();

        app(UpdateUserProfileInformation::class)->update($user, ['name' => $user->name, 'email' => strtoupper($user->email)]);

        $this->assertSame([], $this->revocations());
    }

    public function test_hand_over_revokes_former_owners_keys(): void
    {
        $superadmin = $this->owner();
        $superadmin->assignRole(Role::findOrCreate('Superadmin'));
        $superadmin->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate(config('admin.permission')));
        $team = Team::forceCreate(['user_id' => $superadmin->id, 'name' => 'Prepared', 'personal_team' => false]);
        $invitee = $this->owner();
        $invitation = $team->teamInvitations()->create(['email' => $invitee->email, 'role' => 'admin']);
        $invitation->forceFill(['as_owner' => true])->save();

        app(HandOverInvitedTeam::class)->hand($invitee, $invitation->fresh());

        $this->assertContains(['nlp.example', $superadmin->email, $team->posthogId()], $this->revocations());
    }

    public function test_failed_revocation_does_not_leak_the_response(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['*' => Http::response('body with secret', 500)]);
        $job = new \App\Jobs\RevokeApiKeysInNlpApi('jane@acme.example', null);

        try {
            $job->handle();
            $this->fail('expected an exception');
        } catch (\RuntimeException $error) {
            $this->assertStringNotContainsString('secret', $error->getMessage());
        }
    }

    public function test_an_api_without_managed_keys_is_a_failure(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['*' => Http::response(['detail' => 'Not Found'], 404)]);

        $this->expectException(\RuntimeException::class);
        (new \App\Jobs\RevokeApiKeysInNlpApi('jane@acme.example', null))->handle();
    }
}
