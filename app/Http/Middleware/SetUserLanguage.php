<?php

namespace App\Http\Middleware;

use Closure;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SetUserLanguage
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $user->language = LaravelLocalization::getCurrentLocale();
            $user->save();
        }

        return $next($request);
    }
}
