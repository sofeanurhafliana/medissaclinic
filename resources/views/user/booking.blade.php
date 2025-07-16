<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Booking - Dental Clinic</title>
    @vite('resources/css/app.css')
</head>
<body>

<div class="user-wrapper">
        <!-- Sidebar -->
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
                    <li><a href="#" class="active">Make a booking</a></li>
                    <li><a href="{{route('user.manage.booking')}}">Manage Booking</a></li>
                    <li><a href="{{ route('user.settings') }}">Settings</a></li>
                    <li>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>
        
<div class="user-booking">
    <h1>Make a Booking</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Oops!</strong> Please fix the following issues:<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.booking.review') }}" method="POST">
        @csrf

        <!-- Branch -->
        <label for="branch">Branch:</label>
        <select name="branch" id="branch" class="form-select" required>
            <option value="" disabled selected>Select a Branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select><br><br>

        <!-- Doctor -->
        <label for="doctor">Doctor:</label>
        <select name="doctor_id" id="doctor" class="form-select" required disabled>
            <option value="" disabled selected>Select a Doctor</option>
        </select><br><br>

        <!-- Hidden input for user_id -->
        <input type="hidden" name="user_id" id="user_id">

        <!-- Service -->
        <label for="service">Service:</label>
        <select name="service" id="service" class="form-select" required>
            <option value="" disabled selected>Select a Service</option>
            @foreach($services as $service => $duration)
                <option value="{{ $service }}" data-duration="{{ $duration }}">{{ $service }}</option>
            @endforeach
        </select><br><br>

        <!-- Booking Date -->
        <label for="booking_date">Booking Date:</label>
        <input type="date" name="booking_date" id="booking_date" min="{{ date('Y-m-d') }}" required>
        <br><br>

        <!-- Time Slots -->
        <label for="booking_time">Available Time Slots:</label>
        <div id="time-slots" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
        <input type="hidden" name="booking_time" id="booking_time" required><br><br>

        <!-- Notes -->
        <label for="notes">Notes (Optional):</label>
        <textarea name="notes" id="notes" rows="4" cols="50"></textarea>
        <br><br>

        <button type="submit" class="btn btn-success">Confirm & Pay RM10 Deposit</button>
        <button type="button" onclick="window.location.href='{{ route('dashboard.user') }}'" class="btn">Back</button>
    </form>
</div>

<script>
    const doctors = @json($doctorsByBranch);
    const holidays = @json($holidays);
    const bookedSlots = @json($bookedSlots);
    const doctorUnavailabilities = @json($unavailabilities);

    const timeSlotsDiv = document.getElementById('time-slots');
    const bookingTimeInput = document.getElementById('booking_time');

    const serviceDurations = {
        "Braces": 60,
        "Whitening": 60,
        "Scaling & Polishing": 30,
        "Extraction": 30,
        "Crown & Bridge": 60,
        "Veneers": 60,
        "Implant": 60,
        "Root Canal Treatment": 60,
        "Filling": 30,
        "Denture": 60,
        "Minor Oral Surgery": 60,
        "Radiology": 30,
        "Kids Treatment": 30,
        "Dental Checkup": 30
    };

    let selectedSlots = [];

    document.getElementById('branch').addEventListener('change', function () {
        const branchId = this.value;
        const doctorSelect = document.getElementById('doctor');
        doctorSelect.innerHTML = '<option disabled selected>Select a Doctor</option>';
        doctorSelect.disabled = false;

        if (doctors[branchId]) {
            doctors[branchId].forEach(doctor => {
                const option = document.createElement('option');
                option.value = doctor.id;
                option.textContent = doctor.name;
                option.dataset.userId = doctor.user_id;
                doctorSelect.appendChild(option);
            });
        }

        checkAndGenerateSlots();
    });

    document.getElementById('doctor').addEventListener('change', function () {
        const selectedUserId = this.options[this.selectedIndex].dataset.userId;
        document.getElementById('user_id').value = selectedUserId;
        checkAndGenerateSlots();
    });

    document.getElementById('service').addEventListener('change', checkAndGenerateSlots);

    document.getElementById('booking_date').addEventListener('change', function () {
        const date = this.value;
        const day = new Date(date).getDay();
        if (holidays.includes(date)) {
            alert("Bookings not allowed on public holidays.");
            this.value = '';
        } else if (day === 0) {
            alert("Clinic is closed on Sundays.");
            this.value = '';
        } else {
            checkAndGenerateSlots();
        }
    });

    function checkAndGenerateSlots() {
        const service = document.getElementById('service').value;
        const date = document.getElementById('booking_date').value;
        const doctor = document.getElementById('doctor').value;

        if (service && date && doctor) {
            generateTimeSlots();
        }
    }

    function resetTimeSlots() {
        timeSlotsDiv.innerHTML = '';
        bookingTimeInput.value = '';
        selectedSlots = [];
    }

    function generateTimeSlots() {
        const service = document.getElementById('service').value;
        const duration = serviceDurations[service] || 30;
        const selectedDate = document.getElementById('booking_date').value;
        const selectedBranch = document.getElementById('branch').value;
        const selectedDoctorId = document.getElementById('doctor').value;

        resetTimeSlots();

        const isToday = selectedDate === new Date().toISOString().split('T')[0];
        const nowMinutes = toMinutes(getCurrentTimeHHMM());

        const startRanges = [['09:00', '12:00'], ['14:00', '17:00']];
        startRanges.forEach(([start, end]) => {
            let current = toMinutes(start);
            const endMin = toMinutes(end);

            while (current + 30 <= endMin) {
                const slotStart = toTime(current);
                const slotEnd = toTime(current + 30);

                // ⛔ Skip past time if booking today
                if (isToday && current < nowMinutes) {
                    current += 30;
                    continue;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = `${slotStart} - ${slotEnd}`;
                btn.className = 'btn time-slot-btn';
                btn.dataset.start = slotStart;
                btn.dataset.minutes = current;

                const conflict = bookedSlots.some(slot => {
                    if (
                        slot.booking_date !== selectedDate ||
                        parseInt(slot.doctor_id) !== parseInt(selectedDoctorId)
                    ) return false;

                    const bookedStart = toMinutes(slot.booking_time.slice(0, 5));
                    const slotDuration = slot.service ? (serviceDurations[slot.service] || 30) : 30;
                    const bookedEnd = bookedStart + slotDuration;
                    const currentEnd = current + duration;

                    return !(currentEnd <= bookedStart || current >= bookedEnd);
                });

                if (conflict) {
                    btn.disabled = true;
                    btn.classList.add('disabled-slot');
                } else {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        handleSlotClick(btn, duration);
                    });
                }

                timeSlotsDiv.appendChild(btn);
                current += 30;
            }
        });
    }

    function handleSlotClick(button, duration) {
        document.querySelectorAll('.time-slot-btn').forEach(btn => btn.classList.remove('selected'));
        selectedSlots = [];

        const selectedMinutes = parseInt(button.dataset.minutes);
        const slotsNeeded = duration / 30;
        const allButtons = Array.from(document.querySelectorAll('.time-slot-btn'));

        for (let i = 0; i < slotsNeeded; i++) {
            const requiredMinutes = selectedMinutes + (i * 30);
            const match = allButtons.find(b => parseInt(b.dataset.minutes) === requiredMinutes && !b.disabled);

            if (!match) {
                alert("Not enough consecutive available slots.");
                bookingTimeInput.value = '';
                return;
            }

            selectedSlots.push(match);
        }

        selectedSlots.forEach(btn => btn.classList.add('selected'));
        bookingTimeInput.value = selectedSlots[0].dataset.start;
    }

    function toMinutes(timeStr) {
        const [h, m] = timeStr.split(':').map(Number);
        return h * 60 + m;
    }

    function toTime(minutes) {
        const h = String(Math.floor(minutes / 60)).padStart(2, '0');
        const m = String(minutes % 60).padStart(2, '0');
        return `${h}:${m}`;
    }

    function getCurrentTimeHHMM() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }
</script>

</div>
</body>
</html>