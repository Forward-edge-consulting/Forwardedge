<?php

namespace App\Mail;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Mail\Mailable;

class FormSubmissionNotification extends Mailable
{
    public function __construct(public Form $form, public FormSubmission $submission)
    {
    }

    public function build(): self
    {
        return $this->subject('New response: ' . $this->form->name)
            ->text('emails.form_notification');
    }
}
