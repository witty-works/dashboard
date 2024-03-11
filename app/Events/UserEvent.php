<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent
{
    use Dispatchable, SerializesModels;

    public $user;
    public $source;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->source = request()->session()->get('login_source');
    }
}
