<?php

namespace Tests\Feature;

use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * The copy speaks as the rest of the dashboard does: German says "du",
 * people are named neutrally or with the gender star (German) and the point
 * médian (French), and the error page and validation messages are
 * translated.
 */
class CopyConsistencyTest extends TestCase
{
    /**
     * Every string of the dashboard's own group files in a language, keyed
     * by "file.key"; Laravel's validation files are checked separately.
     *
     * @return array<string, string>
     */
    private function strings(string $locale): array
    {
        // The Word add-in's sign-up text, which #1017 rewrites.
        $elsewhere = ['content.office_register_description', 'content.office_register_summary'];

        $strings = [];
        foreach (glob(lang_path("{$locale}/*.php")) as $file) {
            $group = basename($file, '.php');
            if (str_starts_with($group, 'validation')) {
                continue;
            }
            $values = new RecursiveIteratorIterator(new RecursiveArrayIterator(require $file));
            foreach ($values as $key => $value) {
                if (is_string($value) && ! in_array("{$group}.{$key}", $elsewhere, true)) {
                    $strings["{$group}.{$key}"] = preg_replace('/<[^>]*>/', ' ', $value);
                }
            }
        }

        return $strings;
    }

    public function test_german_says_du(): void
    {
        $formal = [];
        foreach ($this->strings('de') as $key => $text) {
            // The formal pronouns; "Sie" only where no sentence starts, as
            // there it may mean "they".
            if (preg_match_all('/(?<![.!?:]\s)(?<!^)\b(Sie|Ihnen|Ihr|Ihre|Ihrem|Ihren|Ihrer|Ihres)\b/u', $text, $matches)) {
                $formal[] = "{$key}: " . implode(', ', $matches[0]);
            }
        }

        $this->assertSame([], $formal, implode("\n", $formal));
    }

    public function test_people_are_named_neutrally_or_with_both_genders(): void
    {
        $patterns = [
            'de' => '/\b(Benutzer|Nutzer|Benutzern|Nutzern|Administrator|Administratoren)\b(?![*:])/u',
            'fr' => '/\b([Uu]tilisateurs?|[Aa]dministrateurs?)\b(?!·)|-vous sûr\b(?!·)/u',
        ];
        $found = [];
        foreach ($patterns as $locale => $pattern) {
            foreach ($this->strings($locale) as $key => $text) {
                if (preg_match_all($pattern, $text, $matches)) {
                    $found[] = "{$locale}: {$key}: " . implode(', ', array_unique($matches[0]));
                }
            }
        }

        $this->assertSame([], $found, implode("\n", $found));
    }

    public function test_the_validation_messages_are_translated(): void
    {
        $english = require lang_path('en/validation.php');
        $untranslated = [];
        foreach (['de', 'fr'] as $locale) {
            foreach (require lang_path("{$locale}/validation.php") as $key => $text) {
                if (is_string($text) && ($english[$key] ?? null) === $text) {
                    $untranslated[] = "{$locale}: validation.{$key}";
                }
            }
        }

        $this->assertSame([], $untranslated, implode("\n", $untranslated));
    }

    public function test_typos_and_english_abbreviations_are_gone(): void
    {
        $wrong = [
            'de' => ['empfielt', 'Gernderstar', 'Kurzgesagt', 'Speicher diese', 'f.e ', 'Punkt Médian'],
            'en' => ['f.e ', 'f.e.'],
        ];
        $found = [];
        foreach ($wrong as $locale => $words) {
            foreach ($this->strings($locale) as $key => $text) {
                foreach ($words as $word) {
                    if (str_contains($text, $word)) {
                        $found[] = "{$locale}: {$key}: {$word}";
                    }
                }
            }
        }

        $this->assertSame([], $found, implode("\n", $found));
    }

    public function test_the_french_link_on_german_endings_leads_to_the_post_on_german(): void
    {
        $fr = require lang_path('fr/guidelines.php');

        $this->assertStringNotContainsString('langage-non-genr', $fr['german_gender_ending']);
        $this->assertStringContainsString('gender-inclusive-writing-in-german', $fr['german_gender_ending']);
    }

    public static function locales(): array
    {
        return [['de'], ['fr']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('locales')]
    public function test_the_error_page_is_translated(string $locale): void
    {
        config(['sentry.dsn' => 'https://public@sentry.example/1', 'app.support_email' => 'help@deployment.example']);
        $this->app->instance('sentry', new class {
            public function getLastEventId(): string
            {
                return 'abc123';
            }
        });
        (new RegisterErrorViewPaths)();
        app()->setLocale($locale);

        $html = view('errors.500')->render();

        foreach (['Server Error', 'Something went wrong', 'Error ID', 'Please quote'] as $english) {
            $this->assertStringNotContainsString($english, $html, "{$locale}: {$english}");
        }
        $this->assertStringContainsString('abc123', $html);
        $this->assertStringContainsString('mailto:help@deployment.example', $html);
    }
}
