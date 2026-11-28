<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * Only superadmins (the `manage users` permission, which also opens the
 * admin pages) may sign in as another user, and nobody may sign in as a
 * superadmin.
 */
class ImpersonationTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    private function user(): User
    {
        return User::factory()->withPersonalTeam()->create();
    }

    /** With two-factor sign-in, which impersonation needs (SuperadminTwoFactorTest). */
    private function superadmin(): User
    {
        return $this->verifiedSuperadmin($this->user());
    }

    public function test_an_ordinary_user_cannot_sign_in_as_another_user(): void
    {
        $user = $this->user();
        $other = $this->user();

        $this->actingAs($user)->get('/en/impersonate/take/' . $other->id)->assertForbidden();

        $this->assertSame($user->id, auth()->id());
    }

    public function test_a_superadmin_can_sign_in_as_an_ordinary_user(): void
    {
        $admin = $this->superadmin();
        $user = $this->user();

        $this->actingAs($admin)->get('/en/impersonate/take/' . $user->id)->assertRedirect();

        $this->assertSame($user->id, auth()->id());
    }

    public function test_nobody_can_sign_in_as_a_superadmin(): void
    {
        $admin = $this->superadmin();
        $other = $this->superadmin();
        $this->verifyTwoFactorSession($admin);

        // The package sends the request back instead of refusing it.
        $this->actingAs($admin)->get('/en/impersonate/take/' . $other->id)->assertRedirect();

        $this->assertSame($admin->id, auth()->id());
    }
}
