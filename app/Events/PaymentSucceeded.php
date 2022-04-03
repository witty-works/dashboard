<?php

namespace App\Events;

use App\Models\Team;
use Laravel\Cashier\Invoice;

class PaymentSucceeded
{
    public $team;

    public $invoice;

    public function __construct(Team $team, Invoice $invoice)
    {
        $this->team = $team;
        $this->invoice = $invoice;
    }
}
