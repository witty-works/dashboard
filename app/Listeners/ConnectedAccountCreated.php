<?php

namespace App\Listeners;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use JoelButcher\Socialstream\Events\ConnectedAccountCreated as ConnectedAccountCreatedEvent;
use Laravel\Jetstream\Contracts\AddsTeamMembers;

class ConnectedAccountCreated
{
    public function handle(ConnectedAccountCreatedEvent $event)
    {
        $user = $event->connectedAccount->user;

        $invitation = TeamInvitation::where('email', '=', $user->email)->first();
        if ($invitation) {
            app(AddsTeamMembers::class)->add(
                $invitation->team->owner,
                $invitation->team,
                $invitation->email,
                $invitation->role
            );

            $user->switchTeam($invitation->team);

            $invitation->delete();
        } else {
            $this->createTeam($user);
        }
    }

    /**
     * Create a personal team for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user)
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]));
    }
}
