<?php

namespace App\Livewire\Subscription;

use App\Models\Subscription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['saved'];

    public $subscription;

    public function render()
    {
        if (!Gate::check(config('lumki.lumkiPermission'))) {
            abort(403);
        }

        $list = Subscription::all()->sortBy(
            [
                ['company_name', 'asc'],
                ['witty_contract_id', 'asc'],
                ['created_at', 'asc'],
            ]
        );

        $plans = config('stripe.plans');
        $stripe_prices = [];
        foreach ($plans as $plan => $config) {
            if (strpos($config['price_id'], 'price_') === 0) {
                $stripe_prices[$config['price_id']] = $plan;
            }
        }

        return view('livewire.subscription.show', ['list' => $list, 'stripe_prices' => $stripe_prices]);
    }

    public function saved()
    {
        $this->render();
    }

    public function editSubscription(Subscription $subscription)
    {
        if (!Gate::check(config('lumki.lumkiPermission'))) {
            abort(403);
        }

        $this->dispatch('edit', subscription: $subscription);
    }
}
