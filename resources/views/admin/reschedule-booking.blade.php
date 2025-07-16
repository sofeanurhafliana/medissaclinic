<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dental Clinic</title>
    @vite('resources/css/admin.css')

    <!-- FullCalendar Styles -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Clinic Admin</h2>
            <div class="admin-profile">
                @if(Auth::user()->profile_picture)
                    <img src="{{ asset('images/' . Auth::user()->profile_picture) }}" alt="Profile Photo" class="profile-img">
                @else
                    <img src="{{ asset('images/profile_pictures/default.jpg') }}" alt="Default Profile Photo" class="profile-img">
                @endif

                <p class="admin-name">{{ Auth::user()->name }}</p>
                <p class="admin-email">{{ Auth::user()->email }}</p>
            </div>
            <nav>
                <ul>
                    <li><a href="/admin/dashboard">Dashboard</a></li>
                    <li><a href="{{ route('admin.bookings', ['branch_id' => auth()->user()->branch_id]) }}">Manage Bookings</a></li>
                    <li><a href="{{ route('admin.doctors.index') }}">Manage Doctors</a></li>
                    <li><a href="#">Manage Patients</a></li>
                    <li><a href="#">Settings</a></li>
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
        <div class="admin-content">
            <h2>Booking Reschedule</h2>
            <div class="booking-summary" style="background: #fff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1);">
                <h3>Old Booking Details</h3>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}</p>
                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}</p>
                <p><strong>Doctor:</strong> {{ $booking->doctor->name ?? 'N/A'}}</p>
                <p><strong>Service:</strong> {{ $booking->service }}</p>
            </div>

<div class="new-booking-form" style="background: #fff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; box-shadow: 0 1px 5px rgba(0,0,0,0.1);">
    <h3>Enter New Booking Details</h3>
    @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

    <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
        @csrf
        @method('PUT')

        <div style="flex: 1;">
            <label for="booking_date">New Date:</label><br>
            <input type="date" name="booking_date" id="booking_date" required min="{{ now()->format('Y-m-d') }}">
        </div>

        <div style="flex: 1;">
            <label for="booking_time">New Time:</label><br>
            <input type="time" name="booking_time" id="booking_time" required>
        </div>

        <div style="flex: 1;">
            <label for="booking_status">Booking Status:</label><br>
            <select name="booking_status" required>
                <option value="rescheduled">Rescheduled</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn">Update Booking</button>
        </div>
    </form>
</div>


            <!-- Doctor Filter -->
<div style="margin-bottom: 1rem;">
    <label for="doctorFilter"><strong>Filter by Doctor:</strong></label>
    <select id="doctorFilter" style="padding: 8px; border-radius: 6px; margin-left: 0.5rem;">
        <option value="all">All Doctors</option>
        @foreach($doctors as $doctor)
            <option value="{{ $doctor->user->name }}">{{ $doctor->user->name }}</option>
        @endforeach
    </select>
</div>


            <!-- Calendar -->
            <div id="calendar" style="margin-top: 20px; height: 700px;"></div>
        </div>
    </div>

    
    <!-- FullCalendar Script -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>


    <!-- Date Validation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('booking_date');
            const publicHolidays = @json($holidays);

            dateInput.addEventListener('change', function () {
                const selectedDate = new Date(this.value);
                const today = new Date().toISOString().split('T')[0];
                const selectedFormatted = this.value;
                const isSunday = selectedDate.getDay() === 0;

                if (selectedFormatted < today) {
                    alert("You cannot select a past date.");
                    this.value = '';
                } else if (isSunday) {
                    alert("Bookings are not allowed on Sundays.");
                    this.value = '';
                } else if (publicHolidays.includes(selectedFormatted)) {
                    alert("Bookings are not allowed on public holidays.");
                    this.value = '';
                }
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const bookings = @json($bookings);
            const unavailabilities = @json($unavailabilities);

            const doctorColors = {
                "Dr. A": '#007bff',
                "Dr. B": '#28a745',
                "Dr. C": '#ffc107',
            };

            const events = bookings.map(b => ({
                title: b.doctor_name + ': ' + b.service,
                start: b.booking_date + 'T' + b.booking_time,
                backgroundColor: doctorColors[b.doctor_name] || '#888',
                borderColor: '#000',
            }));

            const unavailableEvents = unavailabilities.map(u => ({
                title: u.doctor_name + ' (Unavailable)',
                start: u.start,
                end: u.end,
                backgroundColor: '#ccc',
                borderColor: '#999',
                textColor: '#000',
                display: 'block',
            }));

            const calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialView: 'timeGridWeek',
                events: [...events, ...unavailableEvents],
                height: 479,
                slotMinTime: "09:00:00",
                slotMaxTime: "17:00:00",
                allDaySlot: false,
            });
            console.log("Calendar container:", calendarEl);
console.log("Bookings:", bookings);

            calendar.render();

            // Filter by doctor
            document.getElementById('doctorFilter').addEventListener('change', function () {
                const selectedDoctor = this.value;
                calendar.removeAllEvents();

            const filteredEvents = selectedDoctor === 'all'
                ? [...events, ...unavailableEvents]
                : [...events.filter(e => e.title.startsWith(selectedDoctor)), 
                ...unavailableEvents.filter(e => e.title.startsWith(selectedDoctor))];

                filteredEvents.forEach(e => calendar.addEvent(e));

            });
        });
    </script>
</body>
</html>
