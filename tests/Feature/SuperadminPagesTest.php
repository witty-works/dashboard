<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The navigation renders extra entries behind @can('manage users'), so a page
 * that is fine for an ordinary user can still fail for a superadmin. That is
 * exactly how a link to the removed subscriptions route survived the billing
 * removal: every other test signs in as a plain user.
 */
class SuperadminPagesTest extends TestCase
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

    #[DataProvider('pageProvider')]
    public function test_page_renders_for_a_superadmin(string $uri, string $view): void
    {
        $this->actingAs($this->superadmin())
            ->withoutLocaleRedirects()
            ->get($uri)
            ->assertOk()
            ->assertViewIs($view);
    }

    public static function pageProvider(): array
    {
        return [
            'user profile' => ['/user/profile', 'profile.show'],
            'team show' => ['/team/show', 'teams.show'],
            'team language guidelines' => ['/team/language/dictionary', 'teams.language-guidelines'],
            'academy' => ['/user/academy', 'academy'],
            'analytics' => ['/user/analytics', 'analytics'],
        ];
    }

    public function test_admin_area_is_reachable(): void
    {
        $this->actingAs($this->superadmin())
            ->withoutLocaleRedirects()
            ->get('/admin/users')
            ->assertOk();
    }
}
