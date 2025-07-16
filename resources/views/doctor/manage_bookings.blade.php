<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Bookings</title>
    @vite('resources/css/doctor.css')
</head>
<body>
<div class="doctor-wrapper">
    <aside class="sidebar">
        <h2>Doctor Bookings</h2>
        <div class="doctor-profile">
            @if(Auth::user()->profile_picture)
                <img src="{{ asset('images/' . Auth::user()->profile_picture) }}" alt="Profile Photo" class="profile-img">
            @else
                <img src="{{ asset('images/profile_pictures/default.jpg') }}" alt="Default Profile Photo" class="profile-img">
            @endif

            <p class="doctor-name">{{ Auth::user()->name }}</p>
            <p class="doctor-email">{{ Auth::user()->email }}</p>
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard.doctor') }}">Dashboard</a></li>
                <li><a href="{{ route('doctor.schedule.view') }}">My Schedule</a></li>
                <li><a href="{{ route('doctor.availability.view') }}">Availability</a></li>
                <li><a href="#" class="active">Manage Bookings</a></li>
                <li><a href="{{ route('doctor.settings') }}">Settings</a></li>
                <li>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
    </aside>
<div class="doctor-main-wrapper">
    <div class="doctor-content">
        <h1>Past Bookings</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('doctor.manage.bookings') }}" style="margin-bottom: 20px;">
            <label>From:
                <input type="date" name="start_date" value="{{ request('start_date') }}">
            </label>
            <label>To:
                <input type="date" name="end_date" value="{{ request('end_date') }}">
            </label>
            <button type="submit">Filter</button>
        </form>

        <div class="table-responsive">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->user->name ?? 'N/A' }}</td>
                            <td>{{ $booking->service }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $booking->booking_status)) }}</td>
                            <td>
                                @if(empty($booking->doctor_note))
                                    <form method="POST" action="{{ route('doctor.update.booking.note', $booking->id) }}">
                                        @csrf
                                        <textarea name="doctor_note" rows="2" cols="30" placeholder="Write note...">{{ old('doctor_note', $booking->doctor_note) }}</textarea>
                                        <button type="submit">Save</button>
                                    </form>
                                @else
                                    <div style="white-space: pre-wrap;">{{ $booking->doctor_note }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No past bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
            {{ $bookings->withQueryString()->links('pagination::simple-tailwind') }}
        </div>
    </div>
</div>
</div>
</body>
</html>
