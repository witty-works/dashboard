<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwitchToTeam
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->allTeams()->count() && !$user->currentTeam) {
            $user->switchTeam($user->allTeams()->first());
        }

        return $next($request);
    }
}
