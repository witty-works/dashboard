<?php

namespace App\Http\Middleware;

use Closure;

class SetUserLanguage
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $user->language = app()->getLocale();
            $user->save();
        }

        return $next($request);
    }
}
