<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    use TeamControllerTrait;

    /**
     * Show the team management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']);
        }

        $team = $user->currentTeam;
        if ($team && $user->ownsTeam($team) || $user->hasTeamPermission($team, 'update')) {
            return redirect()->route('teams.language-guidelines');
        }

        return redirect()->route('user.language-guidelines');
    }

    public function mailingConsent(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->has_consented_to_mailing = (bool)$request->get('consent', 0);
            $user->save();
        }

        return redirect()->back();
    }
}
