<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHasTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (!currentUser()->isMemberOfATeam()) {
            return redirect()->route('create-first-team');
        }

        $this->ensureOneOfTheTeamsIsCurrent();

        return $next($request);
    }

    protected function ensureOneOfTheTeamsIsCurrent(): void
    {
        if (!is_null(currentUser()->current_team_id)) {
            return;
        }

        $firstTeamId = currentUser()->allTeams()->first()->id;
        currentUser()->update(['current_team_id' => $firstTeamId]);
    }
}
