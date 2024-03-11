<?php

namespace App\Events;

use Laravel\Jetstream\Events\InvitingTeamMember;

class InvitedTeamMember extends InvitingTeamMember
{
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

        $this->source = request()->session()->get('login_source');
    }
}
