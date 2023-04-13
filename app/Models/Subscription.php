<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $dates = [
        'created_at',
        'renews_at',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'updated_at',
    ];

    public function syncStartRenewalAt()
    {
        $stripeSubscription = $this->asStripeSubscription();

        if ($stripeSubscription) {
            $this->starts_at = $stripeSubscription->current_period_start
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                : null;
            $this->renews_at = $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null;
            $this->save();
        }
    }

    public function planId()
    {
        if (!$this->stripe_price) {
            return 'witty_free';
        }

        $stripePlans = config('stripe.plans');
        if (empty($stripePlans)) {
            return 'witty_free';
        }

        foreach ($stripePlans as $planName => $planConfig) {
            if ($planConfig['price_id'] === $this->stripe_price) {
                return $planName;
            }
        }
    }

    public function planName()
    {
        return __('stripe.' . $this->planId());
    }

    public function isPaidByInvoice()
    {
        return strpos($this->stripe_id, 'invoice') === 0;
    }
}
