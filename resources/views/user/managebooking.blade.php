<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div class="user-wrapper">
    <aside class="sidebar">
        <h2>Medissa Clinic User</h2>
        <div class="user-profile">
            @if(Auth::user()->profile_picture)
                <img src="{{ asset('images/' . Auth::user()->profile_picture) }}" alt="Profile Photo" class="profile-img">
            @else
                <img src="{{ asset('images/profile_pictures/default.jpg') }}" alt="Default Profile Photo" class="profile-img">
            @endif
            <p class="user-name">{{ Auth::user()->name }}</p>
            <p class="user-email">{{ Auth::user()->email }}</p>
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard.user') }}">Dashboard</a></li>
                <li><a href="{{ route('user.booking.create') }}">Make a Booking</a></li>
                <li><a href="#" class="active">Manage Bookings</a></li>
                <li><a href="{{ route('user.settings') }}">Settings</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="dashboard-content">
        <h1>My Bookings</h1>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <!-- Filters -->
        <form method="GET" action="{{ route('user.manage.booking') }}">
            <div class="filters">
            <label>
                From:
                <input type="date" name="from_date" value="{{ request('from_date') }}" onchange="this.form.submit()">
            </label>

            <label>
                To:
                <input type="date" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()">
            </label>
            <label>
                Date:
                <select name="date_filter" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </label>

            <label>
                Service:
                <select name="service" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($services as $service)
                        <option value="{{ $service }}" {{ request('service') === $service ? 'selected' : '' }}>
                            {{ $service }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Doctor:
                <select name="doctor_id" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
            </label>

        <!-- Booking Table -->
        <div class="table-responsive">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->doctor->name ?? 'N/A' }}</td>
                            <td>{{ $booking->service }}</td>
                            <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') : 'N/A' }}</td>
                            <td>{{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('H:i') : 'N/A' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $booking->booking_status)) }}</td>

                                <td>
                                    @if ($booking->payment_status === 'paid_in_full')
                                        Paid
                                    @elseif ($booking->payment_status === 'deposit_paid')
                                        Deposit Paid
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                    @endif
                                </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
