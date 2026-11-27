<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Links point at APP_URL, whatever host a request names. The proxy is
 * trusted for X-Forwarded-Host, which a client can set, and a password reset
 * link built from it carried its token to another host. Not TrustHosts,
 * which would refuse the image's own health check on localhost.
 *
 * Outside a request (queue, console) links come from APP_URL already.
 */
class UseApplicationUrl
{
    public function handle(Request $request, Closure $next)
    {
        if ($root = rtrim((string) config('app.url'), '/')) {
            URL::forceRootUrl($root);
            if ($scheme = parse_url($root, PHP_URL_SCHEME)) {
                URL::forceScheme($scheme);
            }
        }

        return $next($request);
    }
}
