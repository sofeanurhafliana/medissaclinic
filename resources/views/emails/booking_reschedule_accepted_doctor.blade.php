<h2>Hello Dr. {{ $booking->doctor->user->name }},</h2>

<p>The patient <strong>{{ $booking->user->name }}</strong> has accepted the rescheduled booking.</p>

<ul>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Service:</strong> {{ $booking->service }}</li>
</ul>

<p>Thank you for your attention.</p>
