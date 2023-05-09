<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Laravel\Jetstream\TeamInvitation as TeamInvitationModel;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The team invitation instance.
     *
     * @var \Laravel\Jetstream\TeamInvitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     *
     * @param  \Laravel\Jetstream\TeamInvitation  $invitation
     * @return void
     */
    public function __construct(TeamInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $invitor = Auth::user();
        $param = [
            'name' => $invitor->name,
            'email' => $invitor->email,
            'team' => $invitor->currentTeam->name,
            'acceptUrl' => URL::signedRoute('team-invitations.accept-signed', ['invitation' => $this->invitation, 'onboarding' => '1'])
        ];

        return $this->markdown('jetstream::mail.team-invitation', $param)
            ->subject(__('content.team_invitation_subject', $param));
    }
}
