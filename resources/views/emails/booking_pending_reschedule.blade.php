<h2>Booking Needs Rescheduling</h2>

<p>A booking requires attention due to doctor unavailability:</p>

<ul>
    <li><strong>User:</strong> {{ $booking->user->name }}</li>
    <li><strong>Doctor:</strong> {{ $booking->doctor->user->name }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Service:</strong> {{ $booking->service }}</li>
    <li><strong>Branch:</strong> {{ $booking->branchInfo->name ?? 'N/A' }}</li>
</ul>

<p>Please review and reschedule it accordingly.</p>
