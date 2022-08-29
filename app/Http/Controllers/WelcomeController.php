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
         if ($user->invitations->count()) {
            return view('dashboard');
         }

        $team = $this->getCurrentTeam($request);
        if ($team) {
            if ($user->ownsTeam($team) || $user->hasTeamPermission($team, 'update')) {
                return redirect()->route('teams.language-guidelines');
            }

            return redirect()->route('user.language-guidelines');
        }

        return redirect('https://chrome.google.com/webstore/detail/witty/meojhlodfiihbjkcnehkdcgncnhgagog');
    }
}
