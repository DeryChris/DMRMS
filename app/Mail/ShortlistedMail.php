<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Application $application;

    public string $verificationCode;

    public function __construct(Application $application, string $verificationCode)
    {
        $this->application = $application;
        $this->verificationCode = $verificationCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You Have Been Shortlisted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.shortlisted',
        );
    }
}
