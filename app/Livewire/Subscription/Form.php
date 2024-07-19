<?php

namespace App\Livewire\Subscription;

use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use App\Models\Subscription;
use App\Models\Team;
use Stripe\Subscription as StripeSubscription;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $status = [
        '',
        StripeSubscription::STATUS_ACTIVE,
        StripeSubscription::STATUS_CANCELED,
        StripeSubscription::STATUS_TRIALING,
    ];

    public $prices = [
        '',
        'teams',
        'enterprise',
    ];

    public $witty_contract_id;
    public $company_name;
    public $subscription_id;
    public $team_id;
    public $quantity;
    public $stripe_price;
    public $stripe_status;
    public $starts_at;
    public $ends_at;
    public $trial_ends_at;

    protected $listeners = ['edit'];

    protected $rules = [
        'witty_contract_id' => 'string|nullable',
        'company_name' => 'string|nullable',
        'subscription_id' => 'int|nullable',
        'team_id' => 'int',
        'quantity' => 'int|required',
        'stripe_price' => 'required',
        'stripe_status' => 'required',
        'starts_at' => 'date|required',
        'ends_at' => 'date|nullable',
        'trial_ends_at' => 'date|nullable',
    ];

    /**
     * The subscription instance.
     *
     * @var mixed
     */
    public $subscription;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.subscription.form');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->witty_contract_id = '';
        $this->company_name = '';
        $this->subscription_id = '';
        $this->team_id = '';
        $this->quantity = '';
        $this->stripe_price = '';
        $this->stripe_status = '';
        $this->starts_at = '';
        $this->ends_at = '';
        $this->trial_ends_at = '';
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function edit(Subscription $subscription)
    {
        $this->subscription_id = $subscription->id;
        $this->witty_contract_id = $subscription->witty_contract_id;
        $this->company_name = $subscription->company_name;
        $this->team_id = $subscription->owner->id;
        $this->quantity = $subscription->quantity;
        $this->stripe_price = $subscription->stripe_price;
        $this->stripe_status = $subscription->stripe_status;
        $this->starts_at = $subscription->starts_at->format('Y-m-d');
        $this->ends_at = $subscription->ends_at ? $subscription->ends_at->format('Y-m-d') : null;
        $this->trial_ends_at = $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') . ' 23:59:59' : null;

        return $this->render();
    }

    public function storeSubscription()
    {
        $this->validate();

        $team = Team::find($this->team_id);
        if ($team === null) {
            $message = 'No team found for provided team id';
            throw ValidationException::withMessages(['team_id' => $message]);
        }

        if (!Gate::check(config('lumki.lumkiPermission'))) {
            abort(403);
        }

        $subscription = Subscription::find($this->subscription_id);
        if ($subscription === null) {
            $query = Subscription::where(['team_id' => $this->team_id]);
            if ($query->exists()) {
                $message = 'Team already has a subscription';
                throw ValidationException::withMessages(['team_id' => $message]);
            }

            $subscription = new Subscription();
            $subscription->team_id = $this->team_id;
            $subscription->type = 'default';
            $subscription->stripe_id = 'invoice_' . Carbon::now();
        } elseif (!$subscription->isPaidByInvoice()) {
            $message = 'Subscription is not paid by invoice, edit on Stripe';
            throw ValidationException::withMessages(['stripe_price' => $message]);
        }

        $subscription->witty_contract_id = $this->witty_contract_id;
        $subscription->company_name = $this->company_name;
        $subscription->quantity = $this->quantity;
        $subscription->stripe_price = $this->stripe_price;
        $subscription->stripe_status = $this->stripe_status;
        $subscription->starts_at = $this->starts_at;
        $subscription->ends_at = $this->ends_at ? $this->ends_at : null;
        $subscription->trial_ends_at = $this->trial_ends_at ? $this->trial_ends_at : null;

        $subscription->save();

        if ($this->subscription_id) {
            if ($subscription->stripe_status === StripeSubscription::STATUS_CANCELED) {
                event(new SubscriptionCancelled($team));
            } else {
                event(new SubscriptionUpdated($team));
            }
        } else {
            event(new SubscriptionCreated($team));
        }

        $this->dispatch('saved');
        $this->resetForm();
    }
}
