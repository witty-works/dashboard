<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Events\PaymentSucceeded;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use Stripe\Subscription;

class WebhookController extends CashierController
{
    protected function newSubscriptionName(array $payload)
    {
        return 'witty';
    }

    public function handleCheckoutSessionCompleted(array $payload)
    {
        DB::transaction(function () use ($payload) {
            $data = $payload['data']['object'];
            $team = Team::findOrFail($data['client_reference_id']);
            $team->update(['stripe_id' => $data['customer']]);

            $team->subscriptions()->create([
                'name' => $this->newSubscriptionName($payload),
                'stripe_id' => $data['subscription'],
                'stripe_status' => 'active'
            ]);
        });

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        if ($billable = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $subscription = $billable->subscriptions()->where('stripe_id', $payload['data']['object']['id'])->first();

            $oldStatus = $subscription->stripe_status;

            $newStatus = $payload['data']['object']['status'] ?? null;

            parent::handleCustomerSubscriptionUpdated($payload);

            if (
                $newStatus
                && $newStatus == Subscription::STATUS_ACTIVE
                && !in_array($oldStatus, [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIALING])
            ) {
                event(new SubscriptionCreated($billable));

                $billable->update(['trial_ends_at' => null]);
            } else {
                event(new SubscriptionUpdated($billable));
            }
        }

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        if ($billable = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            parent::handleCustomerSubscriptionDeleted($payload);

            event(new SubscriptionCancelled($billable));
        }

        return $this->successMethod();
    }

    protected function handleCustomerDeleted(array $payload)
    {
        if ($billable = $this->getUserByStripeId($payload['data']['object']['id'])) {
            parent::handleCustomerDeleted($payload);

            event(new SubscriptionCancelled($billable));
        }

        return $this->successMethod();
    }

    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        if ($billable = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $invoice = $billable->findInvoice($payload['data']['object']['id']);

            event(new PaymentSucceeded($billable, $invoice));
        }

        return $this->successMethod();
    }
}
