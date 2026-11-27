<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Plain HTTP is sent to HTTPS where the app's own address is HTTPS, as in
 * every deployment; an address on plain HTTP (the browser tests' local
 * server) is served as it is.
 */
class HttpsRedirectTest extends TestCase
{
    public function test_plain_http_is_redirected_when_the_app_is_served_over_https(): void
    {
        config(['app.url' => 'https://dashboard.example']);

        $this->withoutLocaleRedirects()->get('http://dashboard.example/login')
            ->assertRedirect('https://dashboard.example/login');
    }

    public function test_plain_http_is_served_when_the_app_address_is_plain_http(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->withoutLocaleRedirects()->get('http://127.0.0.1:8000/login')->assertOk();
    }
}
