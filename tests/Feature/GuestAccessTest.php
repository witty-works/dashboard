<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Routes reachable without authentication.
 *
 * Assertions name the expected view as well as the status, so a test cannot pass
 * against the fallback error page.
 */
class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Fortify registers these outside the localization group. */
    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_terms_page_renders(): void
    {
        $this->withoutLocaleRedirects()
            ->get('/terms')
            ->assertOk()
            ->assertViewIs('terms');
    }

    public function test_policy_page_renders(): void
    {
        $this->withoutLocaleRedirects()
            ->get('/policy')
            ->assertOk()
            ->assertViewIs('policy');
    }

    public function test_guest_is_redirected_away_from_a_protected_page(): void
    {
        $this->withoutLocaleRedirects()
            ->get('/user/profile')
            ->assertRedirect(route('login'));
    }

    public function test_unknown_url_returns_a_404(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound()
            ->assertViewIs('errors.404');
    }
}
