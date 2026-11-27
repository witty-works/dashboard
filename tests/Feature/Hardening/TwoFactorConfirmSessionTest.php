<?php

namespace Tests\Feature\Hardening;

use App\Models\User;
use App\Support\SuperadminTwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Tests\TestCase;

/**
 * Confirming two-factor sign-in on the profile page marks the session as
 * having seen a code, which opens the admin pages for a superadmin. Like the
 * sign-in challenge, it starts a new session id, so an id someone learned
 * before is not raised to that level.
 */
class TwoFactorConfirmSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_starts_a_new_session_id(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $session = $this->app['session.store'];
        $session->start();
        $before = $session->getId();

        event(new TwoFactorAuthenticationConfirmed($user));

        $this->assertNotSame($before, $session->getId());
        $this->assertSame($user->getAuthIdentifier(), $session->get(SuperadminTwoFactor::SESSION_KEY));
    }
}
