<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The privacy policy and the terms of service are the deployment's: each is a
 * Markdown file whose path the environment gives (PRIVACY_POLICY_FILE,
 * TERMS_OF_SERVICE_FILE). The repository holds no text of its own.
 */
class LegalPagesTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . '/legal-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);
        parent::tearDown();
    }

    public static function pages(): array
    {
        return [
            'privacy policy' => ['/policy', 'app.privacy_policy_file', 'content.privacy_policy_not_published'],
            'terms of service' => ['/terms', 'app.terms_of_service_file', 'content.terms_of_service_not_published'],
        ];
    }

    #[DataProvider('pages')]
    public function test_the_deployments_file_is_shown_as_html(string $url, string $config, string $missing): void
    {
        file_put_contents($this->dir . '/page.md', "## Imprint\n\nSome **Street** 1\n\n[Contact](mailto:office@example.org)\n");
        config([$config => $this->dir . '/page.md']);

        $this->withoutLocaleRedirects()->get($url)->assertOk()
            ->assertSee('<h2>Imprint</h2>', false)
            ->assertSee('<strong>Street</strong>', false)
            ->assertSee('href="mailto:office@example.org"', false)
            ->assertDontSee(__($missing))
            ->assertDontSee('Lorem ipsum');
    }

    #[DataProvider('pages')]
    public function test_html_and_script_links_in_the_file_are_not_passed_on(string $url, string $config, string $missing): void
    {
        file_put_contents($this->dir . '/page.md', "<script>alert(1)</script>\n\n<p onclick=\"x()\">raw</p>\n\n[click](javascript:alert(1))\n");
        config([$config => $this->dir . '/page.md']);

        $html = $this->withoutLocaleRedirects()->get($url)->assertOk()->getContent();

        $this->assertStringNotContainsString('alert(1)</script>', $html);
        $this->assertStringNotContainsString('onclick="x()"', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
    }

    #[DataProvider('pages')]
    public function test_without_a_file_the_page_says_none_is_published(string $url, string $config, string $missing): void
    {
        config([$config => null]);
        $this->withoutLocaleRedirects()->get($url)->assertOk()->assertSee(__($missing))->assertDontSee('Lorem ipsum');

        config([$config => $this->dir . '/does-not-exist.md']);
        $this->withoutLocaleRedirects()->get($url)->assertOk()->assertSee(__($missing));
    }
}
