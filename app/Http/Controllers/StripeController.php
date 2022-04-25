<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

class StripeController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            return redirect(route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']));
        }

        $team = $user->currentTeam;
        if (empty($team)) {
            return redirect(route('teams.create'));
        }

        return $this->portal($request);
    }

    public function portal(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;
        if ($team && $user->ownsTeam($team)) {
            $subscription = $team->subscription();
            if ($subscription) {
                if ($subscription->isPaidByInvoice()) {
                    return redirect('mailto:sales@witty.works');
                }

                return $team->redirectToBillingPortal(
                    $this->teamShowRoute($team),
                    ['locale' => app()->getLocale()]
                );
            }
        }

        return redirect('https://www.witty.works/pricing');
    }

    protected function teamShowRoute(Team $team)
    {
        return route('teams.show', ['team' => $team]);
    }

    protected function getPrice($priceId)
    {
        return Cashier::stripe()->prices->retrieve($priceId, []);
    }
}
