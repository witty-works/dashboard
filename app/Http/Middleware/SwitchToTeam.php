<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwitchToTeam
{
    public function handle(Request $request, Closure $next)
    {

        $this->ensureUserHasCurrentTeam();

        $response = $next($request);

        $this->ensureUserHasCurrentTeam();

        return $response;
    }

    protected function ensureUserHasCurrentTeam()
    {
        $user = Auth::user();
        // every user should have a current team
        if ($user && !$user->currentTeam) {
            $teams = $user->teams;
            if ($teams->count()) {
                // prefer invited teams
                $user->switchTeam($teams->first());
            } elseif (!$user->currentTeam) {
                $ownedTeams = $user->ownedTeams();

                // every user should own a personal team
                if (!$ownedTeams->count()) {
                    $ownedTeams->save(Team::forceCreate([
                        'user_id' => $user->id,
                        'name' => explode(' ', $user->name, 2)[0] . "'s Team",
                        'personal_team' => true,
                    ]));
                }

                // fallback to the personal owned team
                $user->switchTeam($ownedTeams->first());
            }
        }
    }
}
