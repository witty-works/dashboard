<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/** Password sign-in allows five attempts a minute per address and IP. */
class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sixth_wrong_password_in_a_minute_is_refused(): void
    {
        Bus::fake();
        $user = User::factory()->create(['email' => 'jane@acme.example']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'Jane@acme.example', 'password' => 'wrong'])->assertStatus(302);
        }

        $this->post('/login', ['email' => 'jane@acme.example', 'password' => 'password'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_an_address_sent_as_a_list_is_refused_without_an_error(): void
    {
        $this->post('/login', ['email' => ['jane@acme.example'], 'password' => 'wrong'])->assertStatus(302);
    }
}
