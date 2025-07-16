<h2>Admin Notification – Booking Cancelled</h2>

<p>Patient <strong>{{ $booking->user->name }}</strong> has rejected their rescheduled appointment.</p>

<ul>
    <li><strong>Original Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Original Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Doctor:</strong> Dr. {{ $booking->doctor->user->name }}</li>
    <li><strong>Branch:</strong> {{ $booking->branchInfo->name }}</li>
</ul>

<p>The payment has been marked for refund.</p>
