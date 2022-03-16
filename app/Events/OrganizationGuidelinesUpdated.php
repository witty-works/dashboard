<?php

namespace App\Events;

use App\Models\Team;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganizationGuidelinesUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Team
     */
    public $team;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }
}
