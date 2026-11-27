<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A deployment's own legal text (privacy policy, terms of service): a
 * Markdown file outside the repository, mounted where the environment says.
 * The repository holds no such text, so a deployment without the file shows
 * that it has published none.
 */
class LegalDocument
{
    /** Whether the deployment provides its privacy policy and its terms of service. */
    public static function provided(): array
    {
        return [
            'privacy_policy' => self::readable(config('app.privacy_policy_file')),
            'terms_of_service' => self::readable(config('app.terms_of_service_file')),
        ];
    }

    /** Whether there is anything to agree to at sign-up. */
    public static function anyProvided(): bool
    {
        return in_array(true, self::provided(), true);
    }

    private static function readable(?string $path): bool
    {
        return $path !== null && $path !== '' && is_file($path) && is_readable($path);
    }

    /** The file as HTML, or null when none is configured or it cannot be read. */
    public static function html(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (! is_file($path) || ! is_readable($path)) {
            Log::warning('Legal document not readable', ['path' => $path]);

            return null;
        }

        // Written by the operator, but rendered without raw HTML or script
        // links all the same: the page is public.
        return Str::markdown((string) file_get_contents($path), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
