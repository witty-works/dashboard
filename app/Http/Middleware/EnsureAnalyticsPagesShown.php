<?php

namespace App\Http\Middleware;

use App\Support\Analytics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The analytics pages and their API answer 404 while they are switched off
 * (App\Support\Analytics). Checked per request rather than when the routes
 * are registered, so cached routes follow the setting too.
 */
class EnsureAnalyticsPagesShown
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Analytics::pagesShown(), 404);

        return $next($request);
    }
}
