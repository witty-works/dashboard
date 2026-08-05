<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

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
    }

    /**
     * Routes in routes/web.php are registered under a prefix produced by
     * LaravelLocalization::setLocale(), which reads the incoming request. Under a
     * real request the prefix is the locale; in tests the route file is loaded
     * during bootstrap, before any request exists, so setLocale() returns null and
     * the routes register unprefixed. The redirect middleware still insists on a
     * prefix, so every URL bounces to /en, which then matches nothing.
     *
     * Disabling the two redirect filters lets these tests hit the routes as
     * registered. Locale behaviour itself is covered separately in LocalizationTest.
     */
    protected function withoutLocaleRedirects(): static
    {
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);

        return $this;
    }
}
