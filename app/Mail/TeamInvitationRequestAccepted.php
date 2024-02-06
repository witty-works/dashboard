<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TeamInvitationRequest as TeamInvitationRequestModel;

class TeamInvitationRequestAccepted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The team invitation request instance.
     *
     * @var \App\Models\TeamInvitationRequest
     */
    public $invitationRequest;

    /**
     * The role to which the user was accepted to.
     *
     * @var string
     */
    public $role;

    /**
     * The admin that accepted the invitation request instance.
     *
     * @var \App\Models\User
     */
    public $admin;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\TeamInvitationRequest  $invitationRequest
     * @return void
     */
    public function __construct(TeamInvitationRequestModel $invitationRequest, $role, $admin)
    {
        $this->invitationRequest = $invitationRequest;
        $this->role = $role;
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $param = [
            'team' => $this->invitationRequest->team->name,
            'role' => $this->role,
            'name' => $this->admin->name,
            'email' => $this->admin->email,
        ];

        return $this->markdown('emails.team-invitation-request-accepted', $param)
            ->subject(__('content.team_invitation_request_accepted_subject', $param));
    }
}
