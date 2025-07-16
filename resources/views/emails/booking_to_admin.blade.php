<h2>New Booking Alert ({{ $booking->branchInfo->name }})</h2>

<p>A user has made a booking:</p>
<ul>
    <li><strong>User:</strong> {{ $booking->user->name }}</li>
    <li><strong>Service:</strong> {{ $booking->service }}</li>
    <li><strong>Doctor:</strong> Dr. {{ $booking->doctor->user->name }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
</ul>
