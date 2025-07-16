<h2>Admin Notification – Reschedule Accepted</h2>

<p>Patient <strong>{{ $booking->user->name }}</strong> has accepted the reschedule for their appointment at <strong>{{ $booking->branchInfo->name }}</strong>.</p>

<ul>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Doctor:</strong> Dr. {{ $booking->doctor->user->name }}</li>
</ul>

<p>You may proceed accordingly.</p>
