<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Refuses a request the browser marks as started from another site or
 * another subdomain (Sec-Fetch-Site), for GET routes that change state.
 * A request without the header (an old browser, a script) and one the
 * person started themselves (`none`: typed, bookmarked) go through.
 */
class RefuseCrossSiteRequests
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->headers->get('Sec-Fetch-Site'), ['cross-site', 'same-site'], true)) {
            abort(403, __('content.open_from_dashboard'));
        }

        return $next($request);
    }
}
