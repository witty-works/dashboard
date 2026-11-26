<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users;
use App\Models\LanguageGuidelines;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Superadmins find users, see every team a user is in, make or remove
 * superadmins, and delete accounts through DeleteUser, so an admin deletion
 * is as complete as a self-service one.
 */
class UsersPageTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        $user = User::factory()->withPersonalTeam()->create($attributes);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function superadmin(array $attributes = []): User
    {
        return tap($this->user($attributes))->assignRole(config('admin.role'));
    }

    private function page(User $actor)
    {
        return Livewire::actingAs($actor)->test(Users::class);
    }

    public function test_ordinary_users_are_refused(): void
    {
        $user = $this->user();

        $this->actingAs($user)->withoutLocaleRedirects()->get('/admin/users')->assertForbidden();
        // The component's actions can be called directly, without the page.
        Livewire::actingAs($user)->test(Users::class)->assertForbidden();
    }

    public function test_it_finds_users_by_name_or_email(): void
    {
        $admin = $this->superadmin();
        $this->user(['name' => 'Grace Hopper', 'email' => 'grace@example.org']);
        $this->user(['name' => 'Alan Turing', 'email' => 'alan@example.org']);

        $this->page($admin)->set('search', 'hopper')->assertSee('grace@example.org')->assertDontSee('alan@example.org');
        $this->page($admin)->set('search', 'alan@')->assertSee('Alan Turing')->assertDontSee('Grace Hopper');
    }

    public function test_it_lists_every_team_of_a_user_with_the_role(): void
    {
        $admin = $this->superadmin();
        $user = $this->user(['email' => 'member@example.org']);
        $other = $this->user();
        $other->currentTeam->forceFill(['name' => 'Research'])->save();
        $other->currentTeam->users()->attach($user, ['role' => 'admin']);

        $this->page($admin)->set('search', 'member@')
            ->assertSee($user->ownedTeams()->first()->name)
            ->assertSee('Research')
            ->assertSee(__('content.admin_role_owner'))
            ->assertSee(__('content.admin_role_admin'));
    }

    public function test_it_makes_and_removes_a_superadmin(): void
    {
        $admin = $this->superadmin();
        $user = $this->user();

        $this->page($admin)->call('makeSuperadmin', $user->id)->assertHasNoErrors();
        $this->assertTrue($user->fresh()->hasRole(config('admin.role')));

        $this->page($admin)->call('removeSuperadmin', $user->id)->assertHasNoErrors();
        $this->assertFalse($user->fresh()->hasRole(config('admin.role')));
    }

    public function test_the_last_superadmin_cannot_remove_their_role(): void
    {
        $admin = $this->superadmin();

        $this->page($admin)->call('removeSuperadmin', $admin->id)->assertHasErrors('superadmin');

        $this->assertTrue($admin->fresh()->hasRole(config('admin.role')));
    }

    public function test_deleting_goes_through_delete_user(): void
    {
        $admin = $this->superadmin();
        $user = $this->user();
        LanguageGuidelines::forceCreate(['user_id' => $user->id]);
        DB::table('oauth_access_tokens')->insert(['id' => 'token-' . $user->id, 'user_id' => $user->id, 'client_id' => 1, 'revoked' => false]);

        $this->page($admin)->call('deleteUser', $user->id)->assertHasNoErrors();

        $this->assertModelMissing($user);
        $this->assertSame(0, LanguageGuidelines::where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('oauth_access_tokens')->where('user_id', $user->id)->count());
    }

    public function test_deleting_an_owner_whose_team_has_members_is_refused(): void
    {
        $admin = $this->superadmin();
        $owner = $this->user();
        $owner->currentTeam->users()->attach($this->user(), ['role' => 'user']);

        $this->page($admin)->call('deleteUser', $owner->id)->assertHasErrors('team');

        $this->assertNotNull($owner->fresh());
    }

    public function test_superadmins_cannot_delete_themselves_here(): void
    {
        $admin = $this->superadmin();

        $this->page($admin)->call('deleteUser', $admin->id)->assertForbidden();

        $this->assertNotNull($admin->fresh());
    }

    public function test_the_page_offers_impersonation_but_not_of_superadmins(): void
    {
        $admin = $this->superadmin();
        $user = $this->user(['email' => 'plain@example.org']);
        $other = $this->superadmin(['email' => 'other-admin@example.org']);

        $this->page($admin)
            ->assertSee(route('impersonate', $user->id), false)
            ->assertDontSee(route('impersonate', $other->id), false);
    }

    /**
     * Livewire actions can be called with any snapshot of the component,
     * whatever the page showed: each one checks the permission itself.
     */
    #[DataProvider('actions')]
    public function test_an_ordinary_user_cannot_call_the_actions(string $action): void
    {
        $admin = $this->superadmin();
        $user = $this->user();
        $victim = $this->user();
        $component = $this->page($admin);

        $this->actingAs($user);
        $component->call($action, $action === 'makeSuperadmin' ? $user->id : $victim->id)->assertForbidden();

        $this->assertFalse($user->fresh()->hasRole(config('admin.role')));
        $this->assertNotNull($victim->fresh());
    }

    public static function actions(): array
    {
        return [['makeSuperadmin'], ['removeSuperadmin'], ['deleteUser']];
    }
}
