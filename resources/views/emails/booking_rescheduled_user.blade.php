<h2>Dear {{ $booking->user->name }},</h2>

    <p>Your booking for <strong>{{ $booking->service }}</strong> has been rescheduled to:</p>
    <ul>
        <li>Date: {{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}</li>
        <li>Time: {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</li>
    </ul>

    <p>To accept/reject, please login to our website.</p>

    <p>Thank you,<br>Medissa Clinic</p>
