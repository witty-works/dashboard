<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * superadmin:create is the way into a deployment whose sign-up is closed: it
 * runs on the server, creates a verified superadmin and mails them a link to
 * set a password.
 */
class CreateSuperadminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_superadmin_and_mails_a_password_link(): void
    {
        Notification::fake();

        $this->artisan('superadmin:create', ['email' => 'admin@example.org', 'name' => 'Ada Admin'])
            ->assertExitCode(0);

        $user = User::where('email', 'admin@example.org')->firstOrFail();
        $this->assertSame('Ada Admin', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Superadmin'));
        $this->assertTrue($user->can(config('admin.permission')));
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_it_makes_an_existing_account_a_superadmin_without_a_new_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'existing@example.org', 'email_verified_at' => now()]);
        $password = $user->password;

        $this->artisan('superadmin:create', ['email' => 'Existing@Example.org', 'name' => 'Ignored'])
            ->assertExitCode(0);

        $user->refresh();
        $this->assertTrue($user->hasRole('Superadmin'));
        $this->assertSame($password, $user->password);
        Notification::assertNothingSent();
        $this->assertSame(1, User::where('email', 'existing@example.org')->count());
    }

    public function test_it_refuses_an_invalid_email(): void
    {
        $this->artisan('superadmin:create', ['email' => 'not-an-address', 'name' => 'Nobody'])
            ->assertExitCode(1);

        $this->assertSame(0, User::count());
    }

    public function test_it_refuses_an_existing_account_whose_address_is_not_verified(): void
    {
        // Anyone can register any address; the role must not go to whoever
        // registered it first.
        $user = User::factory()->unverified()->create(['email' => 'unverified@example.org']);

        $this->artisan('superadmin:create', ['email' => 'unverified@example.org', 'name' => 'Ignored'])
            ->assertExitCode(1);

        $this->assertFalse($user->fresh()->hasRole(config('admin.role')));
    }

    public function test_a_failed_mail_is_reported_with_the_way_to_set_a_password(): void
    {
        Password::shouldReceive('sendResetLink')->andThrow(new \RuntimeException('mail transport down'));

        $this->artisan('superadmin:create', ['email' => 'admin@example.org', 'name' => 'Ada Admin'])
            ->expectsOutputToContain(route('password.request'))
            ->assertExitCode(1);

        // The account is complete; only the link is missing.
        $this->assertTrue(User::where('email', 'admin@example.org')->firstOrFail()->hasRole(config('admin.role')));
    }

    public function test_a_link_that_was_not_sent_is_reported(): void
    {
        Password::shouldReceive('sendResetLink')->andReturn(Password::RESET_THROTTLED);

        $this->artisan('superadmin:create', ['email' => 'admin@example.org', 'name' => 'Ada Admin'])
            ->expectsOutputToContain(route('password.request'))
            ->assertExitCode(1);
    }
}
