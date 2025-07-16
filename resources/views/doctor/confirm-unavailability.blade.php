<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Availability</title>
    @vite('resources/css/doctor.css')
</head>
<body>
<div class="doctor-wrapper">
    <aside class="sidebar">
        <h2>Availability</h2>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard.doctor') }}">Dashboard</a></li>
                <li><a href="{{ route('doctor.schedule.view') }}">My Schedule</a></li>
                <li><a href="#" class="active">Availability</a></li>
                <li><a href="#">Profile</a></li>
                <li>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
    </aside>
    <div class="confirm-container">
    <h2>Confirm Doctor Unavailability</h2>

    <p>The following approved bookings conflict with your selected unavailable time:</p>

    <table class="table">
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Service</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($conflicts as $booking)
                <tr>
                    <td>{{ $booking->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                    <td>{{ $booking->service }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

        <div class="button-row">
            <form action="{{ route('doctor.schedule.finalize') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Confirm Unavailability</button>
            </form>

            <a href="{{ route('doctor.availability.view') }}" class="btn btn-secondary">Cancel</a>
        </div>
        <p class="booking-note">The booking will be passed to admin for rescheduling purposes.</p>
</div>
</div>
</body>
</html>