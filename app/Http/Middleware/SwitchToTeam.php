<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use App\Support\InviteOnly;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SwitchToTeam
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user instanceof User) {
            if (InviteOnly::enabled()) {
                // Invitations first, so that nobody is given a team, or
                // shown the "not in a team" page, before theirs applies.
                $user->applyAcceptedInvitiations();
                $this->checkTeam($user);
            } else {
                $this->checkTeam($user);
                $user->applyAcceptedInvitiations();
            }
        }

        return $next($request);
    }

    protected function checkTeam($user)
    {
        // A current team the user is no longer in is no current team.
        if ($user instanceof User && $user->currentTeam && ! $user->belongsToTeam($user->currentTeam)) {
            $user->forceFill(['current_team_id' => null])->save();
            $user->unsetRelation('currentTeam');
        }

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

        // Invite only: no personal teams; a user without a team waits for an
        // invitation (EnsureUserHasTeam).
        if (InviteOnly::enabled() && !$ownedTeams->count()) {
            return;
        }

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

        if ($user->licenseTeam === null) {
            $user->license_team_id = $team->id;
            $user->save();
        }
    }
}
