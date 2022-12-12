<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeController extends Controller
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
        $team = $this->getCurrentTeam($request);

        if (!$request->user()->hasTeamPermission($team, 'edit_guidelines')) {
            return redirect(route('profile.show'));
        }

        return view('teams.subscription', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    public function subscribe(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            return $this->redirectToLogin();
        }

        $team = $user->currentTeam;
        if (empty($team)) {
            return redirect(route('teams.create'));
        }

        $response = $this->goToPortal($request);
        if ($response instanceof Response) {
            return $response;
        }

        return redirect()->route('login')->banner(
            __('teams.ask_owner_to_buy_or_leave_to_create_own_team', ['name' => $team->owner->name, 'email' => $team->owner->email]),
        );
    }

    public function portal(Request $request)
    {
        $response = $this->goToPortal($request);
        if ($response instanceof Response) {
            return $response;
        }

        return redirect('https://www.witty.works/pricing');
    }

    public function passwordConfirm(Request $request)
    {
        $request->session()->put('socialstream.previous_url', route('teams.subscription'));

        return $this->redirectToLogin();
    }

    protected function goToPortal(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;
        if (!$team || !$user->ownsTeam($team)) {
            return false;
        }

        $subscription = $team->subscription();
        if ($subscription) {
            if ($subscription->isPaidByInvoice()) {
                return redirect('mailto:sales@witty.works');
            }

            return $team->redirectToBillingPortal(
                route('teams.subscription'),
                ['locale' => app()->getLocale()]
            );
        }

        return $team->redirectToCheckout();
    }

    protected function redirectToLogin()
    {
        return redirect(route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']));
    }
}
