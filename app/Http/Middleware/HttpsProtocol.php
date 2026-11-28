<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{

    public function handle($request, Closure $next)
    {
        // Where the app's own address is HTTPS, as in every deployment. The
        // browser tests' local server (php artisan serve) is plain HTTP.
        if (!$request->secure() && str_starts_with((string) config('app.url'), 'https://')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
