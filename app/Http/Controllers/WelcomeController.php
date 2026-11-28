<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
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
            return redirect()->route('login');
        }

        $route = $user->getTeamRoleName() === 'admin' ? 'teams' : 'user';
        return redirect()->route("$route.language-guidelines");
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
