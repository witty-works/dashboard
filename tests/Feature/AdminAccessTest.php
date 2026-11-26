<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The /admin area is gated by the spatie/laravel-permission "manage users"
 * permission (config/admin.php). Authorisation is the least forgiving thing to
 * get wrong in an upgrade, so both sides of the gate are asserted.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Superadmin']);
        $role->givePermissionTo(Permission::firstOrCreate(['name' => 'manage users']));

        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());
        $user->assignRole($role);

        return $user->refresh();
    }

    public function test_user_without_the_permission_is_denied(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $this->actingAs($user->refresh())
            ->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_guest_is_denied(): void
    {
        $this->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertRedirect(route('login'));
    }

    public function test_superadmin_can_list_users(): void
    {
        $this->actingAs($this->superadmin())
            ->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_lumkis_routes_are_gone(): void
    {
        // Lumki created accounts without an invitation and deleted them
        // without DeleteUser; its roles pages could rename the permission.
        $admin = $this->superadmin();
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($admin)->withoutLocaleRedirects();
        $gone = [404, 405];
        $this->assertContains($this->post('/admin/users', ['name' => 'X', 'email' => 'x@example.org', 'password' => 'secret-password', 'password_confirmation' => 'secret-password'])->status(), $gone);
        $this->assertContains($this->delete('/admin/users/' . $user->id)->status(), $gone);
        $this->assertContains($this->get('/admin/roles')->status(), $gone);
        $this->assertContains($this->get('/admin/permissions')->status(), $gone);

        $this->assertNull(User::where('email', 'x@example.org')->first());
        $this->assertNotNull($user->fresh());
    }

    public function test_superadmins_see_the_admin_menu_entry(): void
    {
        $url = route('admin.users');

        $this->actingAs($this->superadmin())->get('/en/user/profile')->assertSee($url, false);

        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());
        $this->actingAs($user->refresh())->get('/en/user/profile')->assertDontSee($url, false);
    }

    public function test_the_menu_offers_a_way_back_while_impersonating(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $this->actingAs($this->superadmin())->get('/en/impersonate/take/' . $user->id);

        $this->get('/en/user/profile')->assertSee(route('impersonate.leave'), false);
    }

    public function test_permission_is_actually_required_not_merely_the_role(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());
        $user->assignRole(Role::firstOrCreate(['name' => 'Bystander']));

        $this->actingAs($user->refresh())
            ->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertForbidden();
    }
}
