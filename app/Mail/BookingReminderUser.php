<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReminderUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
public function __construct(public $booking) {}

public function build()
{
    return $this->subject('Upcoming Appointment Reminder')
                ->view('emails.reminder_user');
}
}
