@php
$services = [
    "Braces" => 60,
    "Whitening" => 60,
    "Scaling & Polishing" => 30,
    "Extraction" => 30,
    "Crown & Bridge" => 60,
    "Veneers" => 60,
    "Implant" => 60,
    "Root Canal Treatment" => 60,
    "Filling" => 30,
    "Denture" => 60,
    "Minor Oral Surgery" => 60,
    "Radiology" => 30,
    "Kids Treatment" => 30,
    "Dental Checkup" => 30,
];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Dental Clinic</title>
    @vite('resources/css/doctor.css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

</head>
<body>
    <div class="doctor-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="clinic-header">
                <h2 style="text-align: center">Medissa Clinic</h2>
            </div>

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
                    <li><a href="#" class="active">Dashboard</a></li>
                    <li><a href="{{ route('doctor.schedule.view') }}">My Schedule</a></li>
                    <li><a href="{{ route('doctor.availability.view') }}">Availability</a></li>
                    <li><a href= "{{route('doctor.manage.bookings')}}">Manage Bookings</a></li>
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


        <!-- Main Content -->
        <main class="dashboard-content">
            <header class="dashboard-header">
                <h1>Welcome, Dr. {{ Auth::user()->name }}</h1>
                <p>Today is {{ now()->format('l, F j, Y') }}</p>
            </header>

            <section class="dashboard-widgets">
                <div class="widget">
                    <h3>Today's Appointments</h3>
                    <p>{{ $bookings->where('booking_date', today())->count() }}</p>
                </div>
                <div class="widget">
                    <h3>This Week</h3>
                    <p>{{ $bookings->filter(function($b) {
                        return $b->booking_date >= now()->startOfWeek() && $b->booking_date <= now()->endOfWeek();
                    })->count() }}</p>
                </div>
<div class="widget">
    <h3>Total Appointments This Month</h3>
    <p>{{ $allBookingsThisMonth->count() }}</p>
</div>

            </section>


            <!-- Schedule Table -->
            <section class="latest-bookings">
                <h2>Upcoming Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->user->name ?? 'N/A' }}</td>
                            <td>{{ $booking->service }}</td>
                            <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') : 'N/A'}}</td>
                            <td>{{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('H:i') : 'N/A' }}</td>
                            <td>{{ ucfirst($booking->booking_status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5">No upcoming bookings</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            </main>

            <aside class="calendar-sidebar">
                    <div id="mini-calendar"></div>
                </aside>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('mini-calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek',
        height: 'auto',
        headerToolbar: {
            left: 'prev',
            center: 'customTitle',
            right: 'next'
        },
        views: {
            listWeek: {
                titleFormat: { month: 'short', day: 'numeric' } // we'll override this manually
            }
        },
        events: [
                @foreach ($allBookings as $booking)
                {
                    title: {!! json_encode(($booking->user->name ?? 'N/A') . ' - ' . $booking->service) !!},
                    start: '{{ $booking->booking_date }}T{{ $booking->booking_time }}',
                    end: '{{ \Carbon\Carbon::parse($booking->booking_date . " " . $booking->booking_time)->addMinutes($services[$booking->service] ?? 30)->toIso8601String() }}',
                    color: '{{ $booking->booking_status == "approved" ? "#4caf50" : ($booking->booking_status == "pending" ? "#ffc107" : "#f44336") }}'
                },
            @endforeach
        ],
        customButtons: {
            customTitle: {
                text: '', // will be updated below
                click: null
            }
        },
        datesSet: function(info) {
            const start = new Date(info.start);
            const end = new Date(info.end);
            end.setDate(end.getDate() - 1); // subtract 1 because FullCalendar end date is exclusive

            const options = { month: 'short', day: 'numeric' };
            const yearOptions = { year: 'numeric' };

            const rangeText = ` ${start.toLocaleDateString('en-US', options)} - ${end.toLocaleDateString('en-US', options)}, ${end.toLocaleDateString('en-US', yearOptions)}`;

            const customTitleEl = calendarEl.querySelector('.fc-customTitle-button');
            if (customTitleEl) {
                customTitleEl.innerHTML = rangeText;
                customTitleEl.disabled = true;
                customTitleEl.style.background = 'transparent';
                customTitleEl.style.border = 'none';
                customTitleEl.style.cursor = 'default';
                customTitleEl.style.color = '#333';
                customTitleEl.style.fontWeight = 'bold';
                customTitleEl.style.fontSize = '1rem';
            }
        }
    });

    calendar.render();
});
</script>


    </div>
</body>
</html>