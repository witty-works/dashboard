<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Concerns\FakesOfficeTokens;
use Tests\TestCase;

/**
 * People agree to, and the footer links, only the legal documents the
 * deployment provides (PRIVACY_POLICY_FILE, TERMS_OF_SERVICE_FILE), and those
 * are the dashboard's own pages, not the website's.
 */
class LegalConsentTest extends TestCase
{
    use FakesOfficeTokens;
    use RefreshDatabase;

    private const CLIENT_ID = '00000000-0000-0000-0000-00000000c1d0';

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config(['services.microsoft_office.client_id' => self::CLIENT_ID]);
        config(['services.microsoft_office.redirect_uri' => 'https://office.example/done']);
        $this->dir = sys_get_temp_dir() . '/consent-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);
        parent::tearDown();
    }

    private function provide(bool $privacy, bool $terms): void
    {
        foreach (['privacy' => $privacy, 'terms' => $terms] as $name => $provided) {
            $path = null;
            if ($provided) {
                $path = $this->dir . "/$name.md";
                file_put_contents($path, "## $name\n");
            }
            config([$name === 'privacy' ? 'app.privacy_policy_file' : 'app.terms_of_service_file' => $path]);
        }
    }

    private function register(array $extra = [])
    {
        return $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            ...$extra,
        ]);
    }

    public function test_registration_asks_to_agree_to_the_privacy_policy_only(): void
    {
        $this->provide(privacy: true, terms: false);

        $this->withoutLocaleRedirects()->get('/register')
            ->assertSee('href="' . route('policy.show') . '"', false)
            ->assertDontSee('href="' . route('terms.show') . '"', false)
            ->assertSee('id="terms"', false);

        $this->register()->assertSessionHasErrors('terms');
    }

    public function test_registration_without_documents_asks_for_no_agreement(): void
    {
        $this->provide(privacy: false, terms: false);

        $this->withoutLocaleRedirects()->get('/register')->assertDontSee('id="terms"', false);

        $this->register()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_registration_with_both_documents_links_both(): void
    {
        $this->provide(privacy: true, terms: true);

        $this->withoutLocaleRedirects()->get('/register')
            ->assertSee('href="' . route('policy.show') . '"', false)
            ->assertSee('href="' . route('terms.show') . '"', false);
    }

    public function test_the_footer_links_the_deployments_documents_not_the_websites(): void
    {
        $this->provide(privacy: true, terms: false);

        $html = $this->withoutLocaleRedirects()->get('/login')->getContent();

        $this->assertStringContainsString('href="' . route('policy.show') . '"', $html);
        $this->assertStringNotContainsString('href="' . route('terms.show') . '"', $html);
        $this->assertStringNotContainsString('www.witty.works/privacy', $html);
        $this->assertStringNotContainsString('www.witty.works/terms', $html);
        $this->assertStringNotContainsString('www.witty.works/imprint', $html);
    }

    public function test_the_office_sign_up_names_and_links_the_privacy_policy_only(): void
    {
        $this->provide(privacy: true, terms: false);

        $html = $this->post('/en/office-register', ['token' => $this->token(['email' => 'new@example.com', 'xms_edov' => true, 'sub' => 'sub-new'])])
            ->assertOk()->getContent();

        $this->assertStringContainsString('href="' . route('policy.show') . '"', $html);
        $this->assertStringNotContainsString('href="' . route('terms.show') . '"', $html);
        $this->assertStringNotContainsString('www.witty.works/privacy', $html);
        $this->assertStringNotContainsString('www.witty.works/terms', $html);
    }
}
