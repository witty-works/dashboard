<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * The admin users table scrolls inside its own box at 320px, as the other
 * list tables do, instead of making the page scroll sideways.
 */
class AdminUsersTableTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    public function test_the_users_table_scrolls_in_its_own_box(): void
    {
        $role = Role::firstOrCreate(['name' => 'Superadmin']);
        $role->givePermissionTo(Permission::firstOrCreate(['name' => 'manage users']));
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());
        $user->assignRole($role);

        $html = $this->actingAs($this->verifiedSuperadmin($user))
            ->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('#<div class="[^"]*overflow-x-auto[^"]*">\s*<table#', $html);
    }
}
