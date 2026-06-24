<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScreeningReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Application $application;

    public Appointment $appointment;

    public function __construct(Application $application, Appointment $appointment)
    {
        $this->application = $application;
        $this->appointment = $appointment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Screening Appointment Tomorrow',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.screening-reminder',
        );
    }
}
