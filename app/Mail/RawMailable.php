<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RawMailable extends Mailable
{
    use Queueable, SerializesModels;

    private $mailTo;
    private $mailSubject;
    private $replyToMail;
    private $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $content, $replyToMail = null)
    {
        $this->content = $content;
        $this->mailSubject = $subject;
        $this->mailTo = $to;
        $this->replyToMail = $replyToMail;
    }

    /**
     * Build the message.
     *
     * @throws \Exception
     */
    public function build()
    {
        $this->text('mail.raw', ['content' => $this->content]);

        $this->subject($this->mailSubject)
            ->to($this->mailTo);

        if ($this->replyToMail) {
            $this->replyTo($this->replyToMail);
        }
    }
}
