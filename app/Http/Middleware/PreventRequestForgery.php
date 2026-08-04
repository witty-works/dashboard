<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

/**
 * Laravel 13 renamed VerifyCsrfToken to PreventRequestForgery and added
 * request-origin verification via the Sec-Fetch-Site header. The old names
 * survive as deprecated aliases; this extends the current one.
 */
class PreventRequestForgery extends Middleware
{
    /**
     * The URIs that should be excluded from request forgery verification.
     *
     * @var array
     */
    protected $except = [
        'stripe/*',
        '/refresh-token',
        '/user/language/domains',
    ];
}
