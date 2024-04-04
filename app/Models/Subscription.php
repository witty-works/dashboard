<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $casts = [
        'created_at' => 'datetime',
        'renews_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function syncStartRenewalAt()
    {
        if ($this->isPaidByInvoice()) {
            return;
        }

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
        if (!$this->stripe_price || !$this->valid()) {
            return 'witty_free';
        }

        if ('enterprise' === $this->stripe_price) {
            return 'witty_enterprise';
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

        return 'witty_' . $this->stripe_price;
    }

    public function planName()
    {
        return __('stripe.' . $this->planId());
    }

    public function isPaidByInvoice()
    {
        return strpos($this->stripe_id, 'sub_') !== 0;
    }
}
