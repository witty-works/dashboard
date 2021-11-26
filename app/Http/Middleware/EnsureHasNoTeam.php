<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

class EnsureHasNoTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (currentUser()->isMemberOfATeam()) {
            return redirect()->route(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
