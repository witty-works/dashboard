<?php

declare(strict_types=1);

namespace App\Localization;

use Illuminate\Http\Request;
use NielsNumbers\LaravelLocalizer\Contracts\DetectorInterface;

class UserLanguageDetector implements DetectorInterface
{
    /**
     * The user's preference lives in the `language` column (kept current by
     * the SetUserLanguage middleware), not in the `locale` attribute the
     * package's own UserDetector reads.
     */
    public function detect(Request $request): string|array|null
    {
        $language = $request->user()?->getAttribute('language');

        return is_string($language) && $language !== '' ? $language : null;
    }
}
