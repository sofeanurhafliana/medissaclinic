<h2>Booking Cancelled</h2>

<p>Patient <strong>{{ $booking->user->name }}</strong> has cancelled their appointment.</p>

<ul>
    <li><strong>Original Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}</li>
    <li><strong>Original Time:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</li>
    <li><strong>Doctor:</strong> Dr. {{ $booking->doctor->user->name ?? 'N/A' }}</li>
    <li><strong>Branch:</strong> {{ $booking->branchInfo->name ?? 'N/A' }}</li>
</ul>

@if(Str::contains($booking->notes, 'Cancellation Reason:'))
    <p><strong>Reason:</strong> {{ Str::after($booking->notes, 'Cancellation Reason:') }}</p>
@endif
