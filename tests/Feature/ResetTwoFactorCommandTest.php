<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * two-factor:reset is how an operator lets back in someone who lost both
 * their authenticator and their recovery codes: it removes two-factor sign-in
 * from the account, which then has to set it up again.
 */
class ResetTwoFactorCommandTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    public function test_it_removes_two_factor_sign_in_from_the_account(): void
    {
        $user = User::factory()->create(['email' => 'locked@example.org']);
        $this->confirmTwoFactor($user);

        $this->artisan('two-factor:reset', ['email' => 'Locked@Example.org'])
            ->expectsConfirmation('Remove two-factor sign-in from locked@example.org?', 'yes')
            ->assertExitCode(0);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
    }

    public function test_a_superadmin_is_told_to_set_it_up_again(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.org']);
        $this->verifiedSuperadmin($user);

        $this->artisan('two-factor:reset', ['email' => 'admin@example.org'])
            ->expectsConfirmation('Remove two-factor sign-in from admin@example.org?', 'yes')
            ->expectsOutputToContain('set up two-factor sign-in again')
            ->assertExitCode(0);
    }

    public function test_nothing_changes_without_confirmation(): void
    {
        $user = User::factory()->create(['email' => 'locked@example.org']);
        $this->confirmTwoFactor($user);

        $this->artisan('two-factor:reset', ['email' => 'locked@example.org'])
            ->expectsConfirmation('Remove two-factor sign-in from locked@example.org?', 'no')
            ->assertExitCode(1);

        $this->assertTrue($user->refresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_an_unknown_address_is_refused(): void
    {
        $this->artisan('two-factor:reset', ['email' => 'nobody@example.org'])
            ->assertExitCode(1);
    }

    public function test_an_account_without_two_factor_is_left_alone(): void
    {
        User::factory()->create(['email' => 'plain@example.org']);

        $this->artisan('two-factor:reset', ['email' => 'plain@example.org'])
            ->expectsOutputToContain('does not use two-factor sign-in')
            ->assertExitCode(0);
    }
}
