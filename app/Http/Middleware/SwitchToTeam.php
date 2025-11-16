<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Platformsh\ConfigReader\Config;

class SwitchToTeam
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user instanceof User) {
            $this->checkTeam($user);
            $user->applyAcceptedInvitiations();
        }

        return $next($request);
    }

    protected function checkTeam($user)
    {
        // every user should have a current team
        if ($user instanceof User && !$user->currentTeam) {
            $teams = $user->teams;
            if ($teams->count()) {
                // prefer invited teams
                $user->switchTeam($teams->first());
            } elseif (!$user->currentTeam) {
                self::ensureTeam($user);
            }
        }
    }

    public static function ensureTeam(User $user)
    {
        $ownedTeams = $user->ownedTeams();

        if (!$ownedTeams->count()) {
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0] . "'s Team",
                'personal_team' => true,
            ]));
        }

        // fallback to the personal owned team
        $team = $ownedTeams->first();
        $user->switchTeam($team);

        if ($user->licenseTeam === null && !$team->getUserLicensesLimitReached()) {
            $user->license_team_id = $team->id;
            $user->save();
        }
    }
}
