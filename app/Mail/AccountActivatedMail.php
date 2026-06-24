<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Applicant $applicant;

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Activated - Ghana Armed Forces Recruitment',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account-activated',
        );
    }
}
