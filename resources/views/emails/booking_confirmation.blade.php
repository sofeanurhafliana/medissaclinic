<h2>Hello {{ $booking->user->name }},</h2>

<p>Your booking has been confirmed!</p>

<ul>
    <li><strong>Branch:</strong> {{ $booking->branchInfo->name }}</li>
    <li><strong>Date:</strong> {{ $booking->booking_date }}</li>
    <li><strong>Time:</strong> {{ $booking->booking_time }}</li>
    <li><strong>Doctor:</strong> {{ $booking->doctor->user->name }}</li>
</ul>

<p>Thank you for choosing our clinic!</p>
