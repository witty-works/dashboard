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
     * The team invitation instance.
     *
     * @var \App\Models\TeamInvitationRequest
     */
    public $invitationRequest;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\TeamInvitationRequest  $invitationRequest
     * @return void
     */
    public function __construct(TeamInvitationRequestModel $invitationRequest)
    {
        $this->invitationRequest = $invitationRequest;
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
            'name' => $this->invitationRequest->team->owner->name,
            'email' => $this->invitationRequest->team->owner->email,
        ];

        return $this->markdown('mail.team-invitation-request-accepted', $param)
            ->subject(__('content.team_invitation_request_accepted_subject', $param));
    }
}
