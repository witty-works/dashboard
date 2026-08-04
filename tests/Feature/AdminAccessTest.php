<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The /admin area is gated by the spatie/laravel-permission "manage users"
 * permission (config/lumki.php). Authorisation is the least forgiving thing to
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

    public function test_superadmin_can_list_roles(): void
    {
        $this->actingAs($this->superadmin())
            ->withoutLocaleRedirects()
            ->get('/admin/roles')
            ->assertOk();
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
