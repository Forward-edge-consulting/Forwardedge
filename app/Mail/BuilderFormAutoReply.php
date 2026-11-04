<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuilderFormAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $subjectLine,
        protected string $bodyCopy,
        protected string $firstName = 'there'
    ) {
    }

    public function build(): self
    {
        $subject = $this->subjectLine !== '' ? $this->subjectLine : 'Thank you for reaching out';

        return $this->subject($subject)
            ->view('emails.builder_auto_reply')
            ->with([
                'bodyCopy' => $this->bodyCopy,
                'subjectLine' => $subject,
                'firstName' => $this->firstName,
            ]);
    }
}
