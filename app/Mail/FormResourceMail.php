<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** The email that carries a form's resource: a branded message with one download button. */
class FormResourceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $subjectLine,
        protected string $bodyCopy,
        protected string $resourceTitle,
        protected string $downloadUrl,
        protected string $firstName = 'there'
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine !== '' ? $this->subjectLine : 'Your resource from Forward Edge')
            ->view('emails.form_resource')
            ->with([
                'bodyCopy' => $this->bodyCopy,
                'resourceTitle' => $this->resourceTitle,
                'downloadUrl' => $this->downloadUrl,
                'firstName' => $this->firstName,
            ]);
    }
}
