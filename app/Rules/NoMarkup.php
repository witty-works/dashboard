<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Refuses text that an HTML parser would read as markup: a "<" directly followed
 * by a letter (a start tag), "/" (an end tag), "!" (a comment or doctype) or "?"
 * (a processing instruction).
 *
 * Text that passes can still contain a bare "<", as in "a < b". It is for
 * user-written text that other clients display outside the dashboard, such as
 * the explanation the browser extension shows next to a term replacement.
 */
class NoMarkup implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/<[a-zA-Z\/!?]/', $value)) {
            $fail('validation.no_markup')->translate();
        }
    }
}
