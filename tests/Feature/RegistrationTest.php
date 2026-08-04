<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registration runs App\Actions\Fortify\CreateNewUser, which also provisions the
 * user's team. This is the path that the posthog_last_sync NOT NULL column broke,
 * so it is worth covering explicitly.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_registration_provisions_a_team_for_the_new_user(): void
    {
        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'ada@example.com')->firstOrFail();

        $this->assertNotNull($user->currentTeam, 'Registered user has no current team.');
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ])->assertSessionHasErrors('email');
    }
}
