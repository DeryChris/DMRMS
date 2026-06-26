<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Applicant $applicant;
    public string $code;

    public function __construct(Applicant $applicant, string $code)
    {
        $this->applicant = $applicant;
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email - Ghana Armed Forces Recruitment',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.email-verification',
        );
    }
}
