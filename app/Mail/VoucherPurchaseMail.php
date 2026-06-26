<?php

namespace App\Mail;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VoucherPurchaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public Voucher $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Recruitment Voucher - Ghana Armed Forces',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.voucher-purchased',
        );
    }
}
