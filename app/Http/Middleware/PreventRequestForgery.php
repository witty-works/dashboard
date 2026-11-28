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
     * Empty on purpose. The one entry that used to live here,
     * '/user/language/domains', never did anything: the only route at that path
     * is in routes/api.php, which runs under the 'api' middleware group, and
     * this middleware is only in the 'web' group. An exception list that looks
     * like it is protecting an endpoint from CSRF checks it never received is
     * worse than an empty one.
     *
     * @var array
     */
    protected $except = [];
}
