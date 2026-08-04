<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Routes reachable without authentication.
 *
 * Every assertion names the expected view. The application's fallback route
 * renders errors.404 with a 200 status, so asserting only the status code would
 * pass against the error page — see test_unknown_url_renders_the_404_view.
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

    /**
     * Documents current behaviour: the fallback renders the 404 view with a 200
     * status rather than a real 404. Pinned so the upgrade cannot change it
     * unnoticed, and as the reason the tests above assert on view names.
     */
    public function test_unknown_url_renders_the_404_view(): void
    {
        $this->get('/this-route-does-not-exist')
            ->assertOk()
            ->assertViewIs('errors.404');
    }
}
