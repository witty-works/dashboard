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
            return redirect()->route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']);
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

    public function roadmap(Request $request)
    {
        $params = [
            'url' => config('productboard.url'),
            'token' => null,
        ];

        $user = $request->user();
        if ($user) {
            $userData = [
                'email' => $user->email,
                'id' => $user->posthogId(),
                'name' => $user->name,
                'company_name' => $user->currentTeam->name,
            ];

            if (!$user->isSharedEmailAccount()) {
                $userData['company_domain'] = $user->getEmailDomain();
            }

            $params['token'] = JWT::encode($userData, config('productboard.private_key'), 'HS256');
        }

        return view('roadmap', $params);
    }
}
