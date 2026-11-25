<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use NielsNumbers\LaravelLocalizer\Middleware\RedirectLocale;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Several model events dispatch jobs that call third-party APIs, and the
        // sync queue runs them inline. Turn any unfaked outbound request into a
        // test failure rather than a slow or flaky network call.
        Http::preventStrayRequests();

        // The layouts' @googlefonts fetches each font's CSS once and keeps it
        // in storage. A fresh checkout, as in CI, has none yet: answer with an
        // empty stylesheet instead of reaching Google.
        Http::fake(['fonts.googleapis.com/*' => Http::response('', 200, ['Content-Type' => 'text/css'])]);

        // The tests look at the HTML; the scripts and styles are Vite's, built
        // separately (npm run build), and a fresh checkout has none.
        $this->withoutVite();
    }

    /**
     * Tests that hit localized routes with unprefixed URLs would be redirected
     * to the locale-prefixed variant by the localizer middleware. Disabling the
     * redirect lets those tests exercise the route directly. Locale behaviour
     * itself is covered separately in LocalizationTest.
     */
    protected function withoutLocaleRedirects(): static
    {
        $this->withoutMiddleware([
            RedirectLocale::class,
        ]);

        return $this;
    }
}
