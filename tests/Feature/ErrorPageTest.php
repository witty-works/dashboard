<?php

namespace Tests\Feature;

use Tests\TestCase;

/** The 500 page loads nothing from other sites and gives the error's id. */
class ErrorPageTest extends TestCase
{
    public function test_500_page_shows_the_error_id_without_third_party_scripts(): void
    {
        config(['sentry.dsn' => 'https://public@sentry.example/1']);
        $this->app->instance('sentry', new class {
            public function getLastEventId(): string
            {
                return 'abc123def456';
            }
        });

        // As the exception handler does before it renders an error page.
        (new \Illuminate\Foundation\Exceptions\RegisterErrorViewPaths)();
        $html = view('errors.500')->render();

        $this->assertStringContainsString('abc123def456', $html);
        $this->assertStringNotContainsString('sentry-cdn.com', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}
