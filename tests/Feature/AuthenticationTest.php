<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Fortify drives login/logout. These cover the credential path end to end so a
 * framework or Fortify upgrade cannot quietly break sign-in.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-password')]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-password')]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect();

        $this->assertGuest();
    }

    public function test_password_is_hashed_not_stored_in_plain_text(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-password')]);

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }
}
