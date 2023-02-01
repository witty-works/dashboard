<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class Onboarding
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user && !Route::is('download')) {
            $currentRouteName = Route::currentRouteName();
            $routes = [
                'profile.onboarding',
                'profile.onboarding.store',
                'login',
                'logout',
                'mock-login',
                'impersonate',
                'impersonate.leave',
            ];

            if (!$user->hasCompletedOnboarding() && !in_array($currentRouteName, $routes)) {
                return redirect()->route('profile.onboarding');
            }
        }

        return $next($request);
    }
}
