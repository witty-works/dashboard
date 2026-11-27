<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the security headers to every response, pages and JSON alike.
 *
 * The Content-Security-Policy lets the browser load scripts, styles, fonts
 * and images only from the dashboard itself, with these exceptions:
 *
 * - Inline scripts run only with this response's nonce. Vite, Livewire and
 *   the debug bar add it to their tags themselves; views add it with
 *   the cspNonce Blade directive. Inline event handlers (onclick=) cannot
 *   carry a nonce, so the views attach their listeners from scripts.
 * - 'unsafe-eval': the Alpine.js that Livewire bundles evaluates x-data and
 *   x-on expressions with `new Function`. Livewire's csp_safe build would
 *   avoid it, but supports only simple expressions, which the Jetstream
 *   views do not keep to.
 * - Inline styles are allowed: the views, the error pages and the
 *   laravel-embed wrapper use style attributes and <style> blocks.
 * - Images also from the host of APP_MEDIA_URL, where the category icons
 *   and examples in storage/app/data are hosted (the website by default).
 * - The academy page frames YouTube's no-cookie player.
 * - The write page calls the NLP API, and the bundled Sentry SDK sends errors
 *   to the host of the DSN.
 *
 * No page may be framed (frame-ancestors, X-Frame-Options). The Word add-in
 * opens the Office sign-in pages with displayDialogAsync() without
 * displayInIframe, so Office shows them in a window of their own.
 *
 * No form-action: Chrome applies it to the redirects after a form post too,
 * and the Office sign-in and the OAuth consent redirect to the add-in and
 * the browser extensions.
 */
class SecurityHeaders
{
    /** Browser features no page uses. */
    private const PERMISSIONS_POLICY = 'accelerometer=(), camera=(), display-capture=(), geolocation=(), '
        . 'gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), usb=()';

    private const YOUTUBE = 'https://www.youtube-nocookie.com';

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = config('security-headers.csp.enabled') ? Vite::useCspNonce() : null;

        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => self::PERMISSIONS_POLICY,
        ];
        if ($nonce !== null) {
            $name = config('security-headers.csp.report_only')
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';
            $headers[$name] = $this->policy($nonce);
        }

        foreach ($headers as $name => $value) {
            if (!$response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function policy(string $nonce): string
    {
        // Vite's dev server, while `npm run dev` runs.
        $hot = Vite::isRunningHot() ? $this->origin(trim((string) file_get_contents(Vite::hotFile()))) : null;
        $hotSocket = $hot === null ? null : preg_replace('/^http/', 'ws', $hot);

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-$nonce'", "'unsafe-eval'", $hot],
            'style-src' => ["'self'", "'unsafe-inline'", $hot],
            'img-src' => ["'self'", 'data:', $this->origin((string) config('app.media_url'))],
            'font-src' => ["'self'", $hot],
            'connect-src' => [
                "'self'",
                $this->origin((string) config('app.nlp_api_public_url')),
                $this->origin((string) config('sentry.dsn')),
                $hot,
                $hotSocket,
            ],
            'frame-src' => [self::YOUTUBE],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'report-uri' => [$this->reportUri((string) config('security-headers.csp.report_uri'))],
        ];

        $policy = [];
        foreach ($directives as $name => $sources) {
            $sources = array_unique(array_filter($sources));
            if ($sources !== []) {
                $policy[] = $name . ' ' . implode(' ', $sources);
            }
        }

        return implode('; ', $policy);
    }

    /** scheme://host[:port] of a URL, without any user info or path; null if there is none. */
    private function origin(string $url): ?string
    {
        $parts = parse_url($url);
        if (!isset($parts['scheme'], $parts['host']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            return null;
        }
        // A host only: a space, `;` or `*` in it would add sources or directives.
        if (filter_var($parts['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            return null;
        }

        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    /** The report address if it is a plain https URL; `;`, `,` or a space would split or extend the policy. */
    private function reportUri(string $uri): ?string
    {
        if ($uri === '' || preg_match('/[;,\s]/', $uri) || parse_url($uri, PHP_URL_SCHEME) !== 'https') {
            return null;
        }

        return filter_var($uri, FILTER_VALIDATE_URL) !== false ? $uri : null;
    }
}
