<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Absorbed from joelbutcher/socialstream, which was archived upstream in
 * December 2025 and never supported Laravel 13.
 */
abstract class ConnectedAccountEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public mixed $connectedAccount)
    {
        //
    }
}
