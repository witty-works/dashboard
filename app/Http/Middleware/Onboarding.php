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
                'impersonate',
                'impersonate.leave',
            ];

            $manager = app('impersonate');
            if (
                !$user->hasCompletedOnboarding()
                && !in_array($currentRouteName, $routes)
                && !$manager->isImpersonating()
            ) {
                return redirect()->route('profile.onboarding');
            }
        }

        return $next($request);
    }
}
