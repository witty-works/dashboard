<?php

namespace App\Events;

use Laravel\Jetstream\Events\InvitingTeamMember;

class InvitedTeamMember extends InvitingTeamMember
{
    use SourceTrait;

    public $source;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $team
     * @param  mixed  $user
     * @return void
     */
    public function __construct($team, $email, $role)
    {
        parent::__construct($team, $email, $role);

        $this->source = $this->getSource($team->owner);
    }
}
