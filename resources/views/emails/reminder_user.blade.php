<h2>Hello {{ $booking->user->name }},</h2>

<p>This is a reminder for your appointment tomorrow:</p>
<ul>
    <li><strong>Branch:</strong> {{ $booking->branchInfo->name }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Doctor:</strong> {{ $booking->doctor->user->name }}</li>
    <li><strong>Service:</strong> {{ $booking->service }}</li>
</ul>

<p>Please arrive 10 minutes early. See you soon!</p>
