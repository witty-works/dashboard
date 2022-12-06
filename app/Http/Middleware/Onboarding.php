<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class Onboarding
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $currentRouteName = Route::currentRouteName();
            $routes = [
                'profile.onboarding',
                'profile.onboarding.store',
                'login',
                'logout',
                'mock-login',
            ];

            if (!$user->role && !in_array($currentRouteName, $routes)) {
                return redirect()->route('profile.onboarding');
            }
        }

        return $next($request);
    }
}
