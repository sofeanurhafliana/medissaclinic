<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Booking - Dental Clinic</title>
    @vite('resources/css/app.css')
</head>
<body>

<div class="user-booking">
    <h1>Reschedule Your Booking</h1>

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

    <form action="{{ route('user.booking.reschedule', $booking->id) }}" method="POST">
        @csrf

        <!-- Branch (Read-only) -->
        <label for="branch">Branch:</label>
        <input type="text" class="form-control" value="{{ $booking->branchInfo->name ?? 'N/A' }}" readonly>
        <br><br>

        <!-- Doctor Selection -->
        <label for="doctor_id">Doctor:</label>
        <select name="doctor_id" id="doctor" class="form-select" required>
            <option value="" disabled>Select a Doctor</option>
            @foreach($branches as $branch)
                @foreach($branch->doctors as $doc)
                    <option value="{{ $doc->id }}" {{ $doc->id == $booking->doctor_id ? 'selected' : '' }}>
                        {{ $doc->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        <br><br>

        <!-- Service (Read-only) -->
        <label for="service">Service:</label>
        <input type="text" class="form-control" value="{{ $booking->service }}" readonly>
        <br><br>

        <!-- New Date -->
        <label for="new_booking_date">New Booking Date:</label>
        <input type="date" name="new_booking_date" id="new_booking_date" class="form-control" min="{{ date('Y-m-d') }}" required>
        <br><br>

        <!-- Time Slot -->
        <label for="new_booking_time">Available Time Slots:</label>
        <div id="time-slots" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
        <input type="hidden" name="new_booking_time" id="new_booking_time" required>
        <br><br>

        <!-- Reason -->
        <label for="reason">Reason for Rescheduling:</label>
        <textarea name="reason" id="reason" rows="4" cols="50" required></textarea>
        <br><br>

        <button type="submit" class="btn btn-primary">Submit Reschedule</button>
        <button type="button" onclick="window.location.href='{{ route('user.manage.bookings') }}'" class="btn">
            Back to My Bookings
        </button>
    </form>
</div>

<script>
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

    const selectedService = "{{ $booking->service }}";
    const duration = serviceDurations[selectedService] || 30;

    const timeSlotsDiv = document.getElementById('time-slots');
    const bookingTimeInput = document.getElementById('new_booking_time');

    document.getElementById('new_booking_date').addEventListener('change', function () {
        const date = new Date(this.value);
        if (date.getDay() === 0) {
            alert("Clinic is closed on Sundays. Please choose another day.");
            this.value = '';
        } else {
            generateTimeSlots();
        }
    });

    function generateTimeSlots() {
        timeSlotsDiv.innerHTML = '';
        bookingTimeInput.value = '';

        const startTimes = [
            ['09:00', '12:00'],
            ['14:00', '17:00']
        ];

        startTimes.forEach(range => {
            let current = toMinutes(range[0]);
            const end = toMinutes(range[1]);

            while (current + duration <= end) {
                const slotStart = toTime(current);
                const slotEnd = toTime(current + duration);
                const label = `${slotStart} - ${slotEnd}`;

                const btn = document.createElement('button');
                btn.textContent = label;
                btn.type = 'button';
                btn.className = 'btn';

                btn.addEventListener('click', () => {
                    bookingTimeInput.value = slotStart;
                    document.querySelectorAll('#time-slots button').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                });

                timeSlotsDiv.appendChild(btn);
                current += 30;
            }
        });
    }

    function toMinutes(timeStr) {
        const [h, m] = timeStr.split(':').map(Number);
        return h * 60 + m;
    }

    function toTime(minutes) {
        const h = Math.floor(minutes / 60).toString().padStart(2, '0');
        const m = (minutes % 60).toString().padStart(2, '0');
        return `${h}:${m}`;
    }
</script>

</body>
</html>
