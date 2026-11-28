<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Every text the dashboard shows exists in German and French, and says the
 * same thing there: no key shown raw, no English string left untranslated,
 * no placeholder or plural form the English text no longer has.
 */
class TranslationCompletenessTest extends TestCase
{
    private const LOCALES = ['de', 'fr'];

    /**
     * The literal keys passed to __(), @lang() and trans() in the views and
     * the application code, with the files that use them.
     *
     * @return array<string, list<string>>
     */
    private function usedKeys(): array
    {
        $keys = [];
        foreach (['resources/views', 'app'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir)));
            foreach ($files as $file) {
                if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }
                $source = file_get_contents($file->getPathname());
                $pattern = '/(?:__|@lang|trans|Lang::get)\(\s*([\'"])((?:(?!\1).|\\\\\1)+)\1\s*[,)]/';
                if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $key = stripslashes($match[2]);
                        // A key built at runtime is not a literal.
                        if (! str_contains($key, '$') && ! str_contains($key, '{{')) {
                            $keys[$key][] = str_replace(base_path() . '/', '', $file->getPathname());
                        }
                    }
                }
            }
        }

        return $keys;
    }

    public function test_every_key_the_code_uses_exists_in_every_language(): void
    {
        $missing = [];
        foreach ($this->usedKeys() as $key => $files) {
            $locales = self::LOCALES;
            // A group key (content.register) must exist in English too; an
            // English sentence is its own English text.
            if (preg_match('/^[a-z_-]+\.[a-z0-9_.-]+$/i', $key)) {
                $locales[] = 'en';
            }
            foreach ($locales as $locale) {
                if (! Lang::hasForLocale($key, $locale)) {
                    $missing[] = "{$locale}: {$key} (" . implode(', ', array_unique($files)) . ')';
                }
            }
        }

        $this->assertSame([], $missing, "Texts without a translation:\n" . implode("\n", $missing));
    }

    /**
     * Every translation in a group file, flattened to dotted keys.
     *
     * @return array<string, string>
     */
    private function groupStrings(string $locale): array
    {
        $strings = [];
        foreach (glob(lang_path("{$locale}/*.php")) as $file) {
            $group = basename($file, '.php');
            // Laravel's own validation messages leave the English inline
            // variants without placeholders on purpose.
            if (str_starts_with($group, 'validation')) {
                continue;
            }
            $flatten = function (array $values, string $prefix) use (&$flatten, &$strings) {
                foreach ($values as $key => $value) {
                    if (is_array($value)) {
                        $flatten($value, "{$prefix}{$key}.");
                    } elseif (is_string($value)) {
                        $strings["{$prefix}{$key}"] = $value;
                    }
                }
            };
            $flatten(require $file, "{$group}.");
        }

        return $strings;
    }

    private static function placeholders(string $text): array
    {
        // Not after a letter: "Kurz gesagt:<li>Wir" is no placeholder.
        preg_match_all('/(?<![\\w:\/]):([a-z_]+)/i', preg_replace('/<[^>]*>/', ' ', $text), $matches);
        $names = array_map('strtolower', $matches[1]);
        sort($names);

        return array_values(array_unique($names));
    }

    private static function isPlural(string $text): bool
    {
        return (bool) preg_match('/(^|\|)\s*(\{\d+\}|\[\d+,(\d+|\*)\])/', $text);
    }

    public function test_translations_keep_the_placeholders_and_plural_forms_of_the_english_text(): void
    {
        $english = $this->groupStrings('en');
        $problems = [];
        foreach (self::LOCALES as $locale) {
            foreach ($this->groupStrings($locale) as $key => $text) {
                if (! isset($english[$key])) {
                    continue;
                }
                if (self::isPlural($text) !== self::isPlural($english[$key])) {
                    $problems[] = "{$locale}: {$key} is " . (self::isPlural($text) ? '' : 'not ') . 'a plural form, unlike the English text';
                }
                $extra = array_diff(self::placeholders($text), self::placeholders($english[$key]));
                if ($extra !== []) {
                    $problems[] = "{$locale}: {$key} has :" . implode(', :', $extra) . ', which the English text does not';
                }
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    public static function guestPages(): array
    {
        return [
            'login' => ['login'],
            'register' => ['register'],
            'forgot password' => ['forgot-password'],
            'reset password' => ['reset-password/token?email=someone@example.org'],
        ];
    }

    /**
     * @dataProvider guestPages
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('guestPages')]
    public function test_a_guest_page_shows_no_raw_translation_key(string $path): void
    {
        $this->withoutLocaleRedirects();
        foreach (array_merge(['en'], self::LOCALES) as $locale) {
            $html = $this->withHeader('Accept-Language', $locale)->get("/{$path}")->assertOk()->getContent();
            $this->assertStringContainsString("lang=\"{$locale}\"", $html, "/{$path} was not rendered in {$locale}");
            $text = strip_tags(preg_replace('#<(script|style)\b.*?</\1>#s', '', $html));

            $this->assertDoesNotMatchRegularExpression(
                '/\b(content|teams|guidelines|auth|passwords|validation)\.[a-z][a-z0-9_]+\b/',
                $text,
                "/{$path} in {$locale} shows a translation key"
            );
        }
    }
}
