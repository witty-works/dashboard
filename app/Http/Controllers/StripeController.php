<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                    route('dashboard'),
                    ['locale' => app()->getLocale()]
                );
            }

            return $team
                ->allowPromotionCodes()
                ->checkout(
                    [[
                        'price' => config('stripe.plans.witty_teams.price_id'),
                        'quantity' => $team->getTotalUserCount()
                    ]],
                    [
                        'success_url' => route('dashboard'),
                        'cancel_url' => route('teams.show', ['team' => $team]),
                        'mode' => 'subscription'
                    ]
                )->redirect();
        }

        return redirect('https://www.witty.works/pricing');
    }
}
