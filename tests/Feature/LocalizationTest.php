<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The dashboard ships German, English and French. A missing translation file
 * surfaces as a raw key in the UI rather than an error, so it is worth asserting
 * that each locale actually resolves.
 */
class LocalizationTest extends TestCase
{
    public function test_the_expected_locales_are_configured(): void
    {
        $this->assertSame(
            ['de', 'en', 'fr'],
            config('localizer.supported_locales')
        );
    }

    public function test_default_locale_is_english(): void
    {
        $this->assertSame('en', config('app.locale'));
    }

    #[DataProvider('localeProvider')]
    public function test_translations_resolve_for_each_locale(string $locale): void
    {
        App::setLocale($locale);

        foreach (['auth', 'content', 'passwords', 'teams', 'validation'] as $group) {
            $this->assertTrue(
                Lang::has($group . '.' . array_key_first(Lang::get($group))),
                "Locale [$locale] does not resolve the [$group] translation group."
            );
        }
    }

    #[DataProvider('localeProvider')]
    public function test_failed_login_message_is_translated(string $locale): void
    {
        App::setLocale($locale);

        $this->assertNotSame('auth.failed', trans('auth.failed'));
    }

    public static function localeProvider(): array
    {
        return [
            'german' => ['de'],
            'english' => ['en'],
            'french' => ['fr'],
        ];
    }
}
