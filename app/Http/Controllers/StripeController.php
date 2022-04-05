<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

class StripeController extends Controller
{
    public function index(Request $request)
    {
        $stripePlans = config('stripe.plans');

        $team = null;
        $plans = [];
        if (is_array($stripePlans)) {
            $user = $request->user();
            $team = $user ? $user->currentTeam : null;

            foreach ($stripePlans as $planName => $planConfig) {
                $planConfig['features'] = $planConfig['features'] ?? [];
                foreach ($planConfig['features'] as $name => $featureConfig) {
                    $planConfig['features'][$name] = trans_choice(
                        'stripe.feature_' . $name,
                        $featureConfig['count'] ?? 0,
                        $featureConfig
                    );
                }

                if (!isset($planConfig['price'])) {
                    $planConfig['price'] = $this->getPrice($planConfig['price_id'])->unit_amount / 12;
                }

                if ($planConfig['checkout'] && $team && !$team->subscribed()) {
                    $planConfig['checkout'] = $team->allowPromotionCodes()
                        ->checkout(
                            [[
                                'price' => $planConfig['price_id'],
                                'quantity' => $team->total_user_licenses_count
                            ]],
                            [
                                'success_url' => $this->teamShowRoute($team),
                                'cancel_url' => $this->teamShowRoute($team),
                                'mode' => 'subscription'
                            ]
                        );
                }

                $plans[$planName] = $planConfig;
            }
        }

        return view('billing', ['team' => $team, 'plans' => $plans]);
    }

    public function portal(Request $request)
    {
        $team = $request->user()->currentTeam;
        $subscription = $team->subscription();
        if ($subscription) {
            if ($subscription->isPaidByInvoice()) {
                return redirect('mailto:sales@witty.works');
            }

            return $team->redirectToBillingPortal(
                $this->teamShowRoute($team)
            );
        }

        return redirect(route('pricing'));
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
