<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\TeamInvitationRequest as TeamInvitationRequestModel;

class TeamInvitationRequest extends Mailable
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
            'name' => $this->invitationRequest->user->name,
            'email' => $this->invitationRequest->user->email,
            'team' => $this->invitationRequest->team->name,
        ];

        return $this->markdown('mail.team-invitation-request', $param)
            ->subject(__('content.team_invitation_request_subject', $param));
    }
}
