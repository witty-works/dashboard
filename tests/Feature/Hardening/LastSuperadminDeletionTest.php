<?php

namespace Tests\Feature\Hardening;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Tests\TestCase;

/**
 * The last superadmin cannot delete their own account, as they cannot be
 * removed as superadmin on the admin page: nobody would be left to create
 * teams or help people.
 */
class LastSuperadminDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole(config('admin.role'));

        return $user;
    }

    public function test_the_last_superadmin_cannot_be_deleted(): void
    {
        $admin = $this->superadmin();

        try {
            app(DeletesUsers::class)->delete($admin);
            $this->fail('The last superadmin was deleted.');
        } catch (ValidationException $e) {
            $this->assertSame([__('content.admin_last_superadmin')], $e->errors()['team']);
        }

        $this->assertModelExists($admin);
    }

    public function test_a_superadmin_can_go_while_another_stays(): void
    {
        $admin = $this->superadmin();
        $this->superadmin();

        app(DeletesUsers::class)->delete($admin);

        $this->assertModelMissing($admin);
    }
}
