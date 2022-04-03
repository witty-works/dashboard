<?php

namespace App\Events;

use App\Models\Team;

abstract class AbstractSubscription
{
    public $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }
}
