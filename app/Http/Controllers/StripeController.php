<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

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

        if (Gate::denies('view', $team)) {
            abort(403);
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
            if ($request->has('register')) {
                return redirect(route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register']));
            }

            return redirect(route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']));
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

        return $team->subscribe()->redirect();
    }
}
