<?php

namespace Tests\Feature\Hardening;

use Tests\TestCase;

/**
 * Caddy serves the files in public/ (the build, fonts, robots.txt) without
 * PHP, so App\Http\Middleware\SecurityHeaders never sees them. The Caddyfile
 * sets the same headers for those, and only where a response has none yet.
 * The image workflow's smoke test checks them on a running container.
 */
class StaticFileHeadersTest extends TestCase
{
    private const HEADERS = ['X-Frame-Options', 'X-Content-Type-Options', 'Referrer-Policy', 'Permissions-Policy'];

    public function test_the_caddyfile_sets_the_applications_headers_on_static_files(): void
    {
        $caddyfile = file_get_contents(base_path('docker/Caddyfile'));
        $page = $this->withoutLocaleRedirects()->get('/en/login');

        foreach (self::HEADERS as $name) {
            $expected = $page->headers->get($name);
            $this->assertNotEmpty($expected, "The application sends {$name}");
            $this->assertMatchesRegularExpression(
                '/^\s*header\s+\?' . preg_quote($name, '/') . '\s+"' . preg_quote($expected, '/') . '"\s*$/m',
                $caddyfile,
                "docker/Caddyfile sets {$name} as the application does"
            );
        }
    }
}
