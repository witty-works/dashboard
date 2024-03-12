<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent
{
    use Dispatchable, SerializesModels, SourceTrait;

    public $user;
    public $source;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->source = $this->getSource($user);
    }
}
