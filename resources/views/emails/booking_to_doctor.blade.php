<h2>Hello Dr. {{ $booking->doctor->user->name }},</h2>

<p>A new appointment has been booked:</p>
<ul>
    <li><strong>Patient:</strong> {{ $booking->user->name }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Service:</strong> {{ $booking->service }}</li>
</ul>
