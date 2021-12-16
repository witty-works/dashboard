<?php

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && app()->bound('sentry')) {
            \Sentry\configureScope(function (Scope $scope): void {
                $user = [
                    'id' => auth()->user()->id,
                    'email' => auth()->user()->email,
                    'team_id' => auth()->user()->currentTeam ? auth()->user()->currentTeam->id : null,
                    'team' => auth()->user()->currentTeam ? auth()->user()->currentTeam->name : null,
                ];

                $scope->setUser($user);
            });
        }

        return $next($request);
    }
}
