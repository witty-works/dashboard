<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * The confirmation link for deleting an account that has no password, such
 * as one created through Office sign-in: whoever has the session also has to
 * have the mailbox.
 */
class ConfirmAccountDeletion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const VALID_MINUTES = 30;

    public function __construct(public User $user)
    {
    }

    public function build()
    {
        $url = URL::temporarySignedRoute(
            'account.delete.confirm',
            now()->addMinutes(self::VALID_MINUTES),
            ['user' => $this->user->id]
        );

        return $this->markdown('emails.confirm-account-deletion', [
            'url' => $url,
            'minutes' => self::VALID_MINUTES,
        ])->subject(__('content.delete_account_email_subject'));
    }
}
