<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Response;

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

        $response = $this->goToPortal($user);
        if ($response instanceof Response) {
            return $response;
        }

        return redirect(route('dashboard'))->banner(
            __('teams.ask_owner_to_buy_or_leave_to_create_own_team', ['name' => $team->owner->name, 'email' => $team->owner->email]),
        );
    }

    public function portal(Request $request)
    {
        $user = $request->user();
        $response = $this->goToPortal($user);
        if ($response instanceof Response) {
            return $response;
        }

        return redirect('https://www.witty.works/pricing');
    }

    protected function goToPortal(User $user)
    {
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
}
