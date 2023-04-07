<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\TeamInvitation;
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
            $invitation = $this->ensureUserHasCurrentTeam($user);
        } elseif (
            !Route::is('mock-login')
            && config('app.mock_login')
            && (new Config())->branch !== 'dev'
        ) {
            return redirect()->route('mock-login', ['email' => config('app.mock_login')]);
        } else {
            $invitation = null;
        }

        $response = $next($request);
        if ($invitation instanceof TeamInvitation) {
            return redirect()->route(
                'team-invitations.accept',
                ['invitation' => $invitation]
            );
        }

        $this->checkTeam($user);

        return $response;
    }

    protected function ensureUserHasCurrentTeam(User $user)
    {
        if ('team-invitations.accept' === Route::currentRouteName()) {
            return;
        }

        # are there any remaining invitations that were previously accept?
        $invitation = TeamInvitation::where('email', $user->email)
            ->where('accepted', true)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($invitation instanceof TeamInvitation) {
            return $invitation;
        }

        $this->checkTeam($user);
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
        $user->switchTeam($ownedTeams->first());
    }
}
