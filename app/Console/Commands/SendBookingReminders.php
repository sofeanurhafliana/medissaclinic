<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\BookingReminderUser;
use App\Mail\BookingReminderDoctor;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'reminders:send-booking';
    protected $description = 'Send 1-day-before appointment reminders to users and doctors';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $bookings = Booking::with(['user', 'doctor.user', 'branchInfo'])
            ->whereDate('booking_date', $tomorrow)
            ->where('booking_status', 'approved')
            ->get();

        foreach ($bookings as $booking) {
            \Log::info("Reminder — Sending to: " . $booking->user->email);
            Mail::to($booking->user->email)->send(new BookingReminderUser($booking));
            Mail::to($booking->doctor->user->email)->send(new BookingReminderDoctor($booking));
        }

        $this->info('Booking reminders sent for ' . $tomorrow);
    }
}
