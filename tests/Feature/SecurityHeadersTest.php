<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\Concerns\FakesOfficeTokens;
use Tests\TestCase;

/**
 * Every response carries the security headers (App\Http\Middleware\SecurityHeaders).
 * The Content-Security-Policy allows inline scripts only with the response's
 * nonce, so each rendered page is also checked for an inline script without
 * it and for inline event handlers (onclick= and the like), which a nonce
 * cannot allow.
 *
 * No page may be framed, the Office sign-in pages included: the Word add-in
 * opens them with Office.context.ui.displayDialogAsync() without
 * displayInIframe, which Office shows as a separate window.
 */
class SecurityHeadersTest extends TestCase
{
    use FakesOfficeTokens;

    use RefreshDatabase;

    private const CLIENT_ID = '00000000-0000-0000-0000-00000000c1d0';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.microsoft_office.client_id' => self::CLIENT_ID]);
        config(['services.microsoft_office.redirect_uri' => 'https://office.example/done']);
    }

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    /** The headers every response gets, whatever its content type. */
    private function assertSecurityHeaders(TestResponse $response): void
    {
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        foreach (['camera=()', 'microphone=()', 'geolocation=()', 'payment=()', 'usb=()'] as $feature) {
            $this->assertStringContainsString($feature, $response->headers->get('Permissions-Policy'));
        }
        $response->assertHeaderMissing('Content-Security-Policy-Report-Only');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'no Content-Security-Policy header');
        foreach (["default-src 'self'", "object-src 'none'", "base-uri 'self'", "frame-ancestors 'none'"] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
        $this->assertStringNotContainsString("'unsafe-inline'", $this->directive($csp, 'script-src'));
    }

    /** The value of one directive, without its name. */
    private function directive(string $csp, string $name): string
    {
        foreach (explode(';', $csp) as $directive) {
            $parts = preg_split('/\s+/', trim($directive), 2);
            if ($parts[0] === $name) {
                return $parts[1] ?? '';
            }
        }

        $this->fail("the policy has no $name: $csp");
    }

    /**
     * A page: the security headers, every inline script with the policy's
     * nonce, and no inline event handler.
     */
    private function assertPageIsCovered(TestResponse $response): void
    {
        $this->assertSecurityHeaders($response);

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/'nonce-([A-Za-z0-9+\/=_-]+)'/", $this->directive($csp, 'script-src'));
        preg_match("/'nonce-([A-Za-z0-9+\/=_-]+)'/", $csp, $match);
        $nonce = $match[1];

        $html = $response->getContent();
        preg_match_all('/<script\b([^>]*)>/i', $html, $scripts);
        $this->assertNotEmpty($scripts[0], 'the page has no script tag at all');
        foreach ($scripts[1] as $i => $attributes) {
            // A script from another site ('self' covers the app's own host) must be one the policy allows.
            if (preg_match('/\ssrc="(https?:)?\/\/([^\/"]+)/', $attributes, $external)
                && ! in_array($external[2], [parse_url(config('app.url'), PHP_URL_HOST), request()->getHost(), 'localhost'], true)) {
                $this->assertStringContainsString($external[2], $this->directive($csp, 'script-src'), 'a script the policy blocks: ' . $scripts[0][$i]);
                continue;
            }
            if (preg_match('/\ssrc=|type="application\/(ld\+)?json"/', $attributes)) {
                continue;
            }
            $this->assertStringContainsString('nonce="' . $nonce . '"', $attributes, 'an inline script without the nonce: ' . $scripts[0][$i]);
        }

        $this->assertDoesNotMatchRegularExpression('/<[^>]*\son[a-z]+\s*=\s*["\']/i', $html, 'the page has an inline event handler');
    }

    public function test_a_guest_page_sends_the_security_headers(): void
    {
        $this->assertPageIsCovered($this->withoutLocaleRedirects()->get('/login')->assertOk());
    }

    public function test_each_response_gets_a_new_nonce(): void
    {
        $first = $this->withoutLocaleRedirects()->get('/login')->headers->get('Content-Security-Policy');
        $second = $this->withoutLocaleRedirects()->get('/login')->headers->get('Content-Security-Policy');

        $this->assertNotNull($first);
        $this->assertNotSame($first, $second);
    }

    #[DataProvider('authenticatedPageProvider')]
    public function test_an_authenticated_page_sends_the_security_headers(string $uri, string $view): void
    {
        $response = $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get($uri)
            ->assertOk()
            ->assertViewIs($view);

        $this->assertPageIsCovered($response);
    }

    public static function authenticatedPageProvider(): array
    {
        return [
            'user profile' => ['/user/profile', 'profile.show'],
            'write' => ['/write', 'write'],
            'academy' => ['/user/academy', 'academy'],
            'team show' => ['/team/show', 'teams.show'],
            'team category settings' => ['/team/language/customize-witty', 'teams.language-guidelines'],
            'team dictionary' => ['/team/language/dictionary', 'teams.language-guidelines'],
            'user analytics' => ['/user/analytics', 'analytics'],
        ];
    }

    /** The academy's videos are the one frame the dashboard shows. */
    public function test_the_academy_videos_may_be_framed(): void
    {
        $response = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/user/academy')->assertOk();

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/', $response->getContent());
        $this->assertNotNull($csp);
        $this->assertSame('https://www.youtube-nocookie.com', $this->directive($csp, 'frame-src'));
    }

    public function test_a_livewire_update_sends_the_security_headers(): void
    {
        $user = $this->signedInUser();
        $page = $this->actingAs($user)->withoutLocaleRedirects()->get('/team/language/dictionary')->assertOk();

        $this->assertMatchesRegularExpression('/wire:snapshot="([^"]+)"/', $page->getContent());
        preg_match('/wire:snapshot="([^"]+)"/', $page->getContent(), $match);
        $snapshot = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Livewire' => '1'])
            ->postJson(app('livewire')->getUpdateUri(), [
                'components' => [['snapshot' => $snapshot, 'updates' => new \stdClass(), 'calls' => []]],
            ])
            ->assertOk()
            ->assertJsonStructure(['components']);

        $this->assertSecurityHeaders($response);
    }

    #[DataProvider('officePageProvider')]
    public function test_the_office_sign_in_pages_send_the_security_headers(string $uri, string $view): void
    {
        $response = $this->get($uri)->assertOk()->assertViewIs($view);

        $this->assertPageIsCovered($response);
    }

    public static function officePageProvider(): array
    {
        return [
            'office login' => ['/en/office-login', 'office_sso_bridge'],
            'office register' => ['/en/office-register', 'office_sso_bridge'],
            'word add-in' => ['/word-addin', 'word-addin'],
        ];
    }

    public function test_the_office_consent_form_sends_the_security_headers(): void
    {
        Bus::fake();
        User::factory()->withPersonalTeam()->create([
            'email' => 'ada@example.com',
            'has_consented_to_terms_of_service' => false,
        ]);

        $response = $this->post('/en/office-login', [
            'token' => $this->token(['email' => 'ada@example.com', 'xms_edov' => true, 'name' => 'Ada Lovelace']),
        ])->assertOk()->assertViewIs('office_register');

        $this->assertSecurityHeaders($response);
        $this->assertDoesNotMatchRegularExpression('/<[^>]*\son[a-z]+\s*=\s*["\']/i', $response->getContent());
    }

    public function test_the_api_still_answers_with_the_headers(): void
    {
        Bus::fake();
        Passport::actingAs(User::factory()->withPersonalTeam()->create(), [], 'extension');

        $response = $this->putJson('/api/user/language/ignore-words?false_positive=guys')->assertNoContent();
        $this->assertSecurityHeaders($response);

        $response = $this->getJson('/.well-known/jwks.json')->assertOk()->assertJsonStructure(['keys']);
        $this->assertSecurityHeaders($response);
    }

    public function test_report_only_mode_sends_the_policy_in_the_report_only_header(): void
    {
        config(['security-headers.csp.report_only' => true, 'security-headers.csp.report_uri' => 'https://reports.example/csp']);

        $response = $this->withoutLocaleRedirects()->get('/login')->assertOk();

        $response->assertHeaderMissing('Content-Security-Policy');
        $policy = $response->headers->get('Content-Security-Policy-Report-Only');
        $this->assertNotNull($policy);
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString('report-uri https://reports.example/csp', $policy);
        // A report-only frame-ancestors only reports; X-Frame-Options still blocks framing.
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_the_browser_may_reach_the_nlp_api_and_sentry(): void
    {
        config([
            'app.nlp_api_public_url' => 'https://api.example.org/v1/',
            'sentry.dsn' => 'https://public@o1.ingest.sentry.io/2',
        ]);

        $csp = $this->withoutLocaleRedirects()->get('/login')->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertSame(
            "'self' https://api.example.org https://o1.ingest.sentry.io",
            $this->directive($csp, 'connect-src')
        );
    }

    /**
     * A view with an inline script that has no nonce, or with an inline event
     * handler, breaks under the policy. The rendered pages above cover the
     * common paths; this catches the rest.
     */
    public function test_odd_config_values_add_no_sources(): void
    {
        config([
            'app.nlp_api_public_url' => 'https://nlp.example *:443/',
            'sentry.dsn' => 'https://key@sentry.example;script-src *',
            'security-headers.csp.report_uri' => 'javascript://reports.example/%0a',
        ]);

        $policy = $this->withoutLocaleRedirects()->get('/login')->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('*', $policy);
        $this->assertSame(1, substr_count($policy, 'script-src'));
        $this->assertStringNotContainsString('report-uri', $policy);
    }

    public static function badReportUris(): array
    {
        return [['http://reports.example/csp'], ['https://reports.example/csp;script-src *'], ['https://reports.example/a,b'], ['data://text/plain,x']];
    }

    #[DataProvider('badReportUris')]
    public function test_the_report_address_must_be_a_plain_https_url(string $uri): void
    {
        config(['security-headers.csp.report_uri' => $uri]);

        $policy = $this->withoutLocaleRedirects()->get('/login')->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('report-uri', $policy);
    }

    public function test_error_responses_get_the_headers(): void
    {
        config(['app.debug' => false]);
        \Illuminate\Support\Facades\Route::get('/__security-headers-boom', fn () => throw new \RuntimeException('boom'));

        $this->assertSecurityHeaders($this->get('/__no-such-page-at-all')->assertNotFound());
        $this->assertSecurityHeaders($this->get('/__security-headers-boom')->assertStatus(500));
    }

    public function test_no_view_has_an_inline_script_without_the_nonce_or_an_inline_event_handler(): void
    {
        $offenders = [];
        foreach ((new Finder())->files()->in(resource_path('views'))->name('*.blade.php')->notPath('vendor/mail') as $file) {
            $source = $file->getContents();
            preg_match_all('/<script\b([^>]*)>/i', $source, $scripts);
            foreach ($scripts[1] as $attributes) {
                if (!preg_match('/\ssrc=|type="application\/(ld\+)?json"|@cspNonce/', $attributes)) {
                    $offenders[] = $file->getRelativePathname() . ': inline script without @cspNonce';
                }
            }
            if (preg_match('/<[^>]*\son[a-z]+\s*=\s*["\']/i', $source)) {
                $offenders[] = $file->getRelativePathname() . ': inline event handler';
            }
        }

        $this->assertSame([], $offenders);
    }
}
