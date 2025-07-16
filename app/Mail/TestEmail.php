<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email from Laravel',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test', // ✅ use the correct view here
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
