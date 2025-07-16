@php
$services = $services ?? [
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
    <title>Doctor Schedule</title>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    @vite('resources/css/doctor.css')
</head>
<body>

<div class="doctor-wrapper">
    <aside class="sidebar">
        <h2>Doctor Schedule</h2>
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
                <li><a href="#" class="active">My Schedule</a></li>
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

    <div class="content">

        <!-- FullCalendar -->
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "{{ request('view') === 'day' ? 'timeGridDay' : (request('view') === 'week' ? 'timeGridWeek' : 'dayGridMonth') }}",
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            contentHeight: 'auto',
            slotMinTime: "09:00:00",
            slotMaxTime: "17:00:00",
            allDaySlot: false,
            events: [
                @foreach ($bookings as $booking)
                    {
                        title: {!! json_encode(($booking->user->name ?? 'N/A') . ' - ' . $booking->service) !!},
                        start: '{{ $booking->booking_date }}T{{ $booking->booking_time }}',
                        end: '{{ \Carbon\Carbon::parse($booking->booking_date . " " . $booking->booking_time)->addMinutes($services[$booking->service] ?? 30)->toIso8601String() }}',
                        color: '{{ $booking->booking_status == "approved" ? "#4caf50" : ($booking->booking_status == "pending" ? "#ffc107" : "#f44336") }}',
                    },
                @endforeach

                @foreach ($unavailabilities as $unavailable)
                    {
                        title: '{{ $unavailable->note ?? "Unavailable" }}',
                        start: '{{ $unavailable->unavailable_date }}T{{ $unavailable->unavailable_start }}',
                        end: '{{ $unavailable->unavailable_date }}T{{ $unavailable->unavailable_end }}',
                        color: '#9e9e9e', // grey for unavailability
                    },
                @endforeach

                    // Public Holidays with Labels (Greyed out)
                    @foreach ($holidays as $holiday)
                    {
                        title: {!! json_encode($holiday->name) !!},
                        start: '{{ $holiday->date }}',
                        display: 'background',
                        allDay: true,
                        color: '#e0e0e0'
                    },
                    @endforeach

// Label Sundays as "Closed - Sunday"
                    @php
                        use Carbon\Carbon;

                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                    @endphp

                    @for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay())
                        @if ($date->isSunday())
                            {
                                title: 'Closed - Sunday',
                                start: '{{ $date->toDateString() }}',
                                display: 'background',
                                allDay: true,
                                color: '#f0f0f0'
                            },
                        @endif
                    @endfor


                    ]
        });

        calendar.render();
    });
</script>

</body>
</html>