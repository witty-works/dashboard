<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Links the dashboard generates point at APP_URL, whatever host a request
 * names. The proxy is trusted for X-Forwarded-Host, so without this a
 * password reset requested with `X-Forwarded-Host: attacker.example` mailed
 * the victim a link with a valid token to the attacker's host.
 */
class GeneratedUrlHostTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_reset_link_ignores_the_forwarded_host(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'victim@example.org']);

        $this->withoutLocaleRedirects()
            ->withHeaders(['X-Forwarded-Host' => 'attacker.example', 'X-Forwarded-Proto' => 'https'])
            ->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;
            $expected = parse_url(config('app.url'), PHP_URL_HOST);

            return parse_url($url, PHP_URL_HOST) === $expected && !str_contains($url, 'attacker.example');
        });
    }

    public function test_a_page_links_to_the_application_host_whatever_the_request_host(): void
    {
        $this->withoutLocaleRedirects()
            ->withHeaders(['X-Forwarded-Host' => 'attacker.example', 'X-Forwarded-Proto' => 'https'])
            ->get(route('login'))
            ->assertOk()
            ->assertDontSee('attacker.example');
    }
}
