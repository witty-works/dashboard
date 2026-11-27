<?php

namespace Tests\Feature\Accessibility;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Details the first pass of #986 got slightly wrong: the skip link must show
 * above the fixed sidebar, hidden names must not run into their label, and
 * labels built around a name read as sentences in every language.
 */
class ReviewFollowUpsTest extends TestCase
{
    private function zIndex(string $css, string $selector): int
    {
        $this->assertMatchesRegularExpression('/' . preg_quote($selector, '/') . '\s*\{[^}]*z-index:\s*(\d+)/', $css);
        preg_match('/' . preg_quote($selector, '/') . '\s*\{[^}]*z-index:\s*(\d+)/', $css, $match);

        return (int) $match[1];
    }

    public function test_the_focused_skip_link_shows_above_the_sidebar(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertGreaterThan($this->zIndex($css, '.wittyworks-navigation-wrapper'), $this->zIndex($css, '.skip-link:focus'));
    }

    public function test_no_hidden_name_starts_with_its_separating_space(): void
    {
        $found = [];
        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            if (preg_match_all('/<span class="sr-only">\s+\S/', $file->getContents(), $m)) {
                $found[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $found);
    }

    public function test_labels_around_a_name_are_sentences(): void
    {
        foreach (['en', 'de', 'fr'] as $locale) {
            app()->setLocale($locale);
            foreach (['api_keys_grace_for', 'make_owner_of'] as $key) {
                $text = __('content.' . $key, ['name' => 'Editor']);
                $this->assertStringContainsString('Editor', $text, "$locale $key");
                $this->assertStringNotContainsString('content.', $text, "$locale $key");
            }
        }
        app()->setLocale('de');
        $this->assertSame('Registrieren', __('content.register'));
        $this->assertSame('Passwort zurücksetzen', __('content.reset_password'));
    }
}
