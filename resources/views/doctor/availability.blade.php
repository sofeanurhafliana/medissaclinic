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
        <h2>Doctor Availability</h2>
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
                <li><a href="#" class="active">Availability</a></li>
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

    <div class="availability-form">
    <h1>Set Unavailable Time Slots</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following issues:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('doctor.schedule.update') }}" method="POST">

        @csrf

        <label for="unavailable_date">Date:</label>
        <input type="date" id="unavailable_date" name="unavailable_date" min="{{ date('Y-m-d') }}" required><br><br>

        <label for="unavailable_start">Start Time:</label>
        <input type="time" id="unavailable_start" name="unavailable_start" min="09:00" max="17:00" required><br><br>

        <label for="unavailable_end">End Time:</label>
        <input type="time" id="unavailable_end" name="unavailable_end" min="09:00" max="17:00" required><br><br>


        <label for="note">Note (Optional):</label>
        <textarea name="note" id="note" rows="3" placeholder="e.g., Lunch, Surgery, Meeting"></textarea><br><br>

        <button type="submit" class="btn btn-primary">Save Unavailability</button>
        <button type="button" onclick="window.location.href='{{ route('dashboard.doctor') }}'" class="btn">
            Back to Dashboard
        </button>
    </form>

    <hr>

    <h2>Your Unavailable Times</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>From</th>
                <th>To</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($unavailabilities as $slot)
                <tr>
                    <td>{{ $slot->unavailable_date ? \Carbon\Carbon::parse($slot->unavailable_date)->format('d F Y') : 'N/A'}}</td>
                    <td>{{ $slot->unavailable_start ? \Carbon\Carbon::parse($slot->unavailable_start )->format('H:i') : 'N/A'}}</td>
                    <td>{{ $slot->unavailable_end ? \Carbon\Carbon::parse($slot->unavailable_end)->format('H:i') : 'N/A'}}</td>
                    <td>{{ $slot->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No unavailable times set.</td>
                </tr>
            @endforelse
        </tbody>
        <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
            {{ $unavailabilities->withQueryString()->links('pagination::simple-tailwind') }}
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('unavailable_date');
            const publicHolidays = @json($holidays); // assume you pass this from the controller

        dateInput.addEventListener('input', function () {
            const selectedDate = new Date(this.value);
            const day = selectedDate.getDay(); // 0 = Sunday
            const formatted = this.value;

        if (day === 0 || publicHolidays.includes(formatted)) {
            alert("You can't set unavailability on Sundays or public holidays.");
            this.value = '';
        }
    });
});
</script>
    </table>
</div>
</div>
</body>
</html>
