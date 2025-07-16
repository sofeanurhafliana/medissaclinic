<!DOCTYPE html>
<html>
<head>
    <title>Patient History - {{ $patient->name }}</title>
    @vite('resources/css/admin.css')
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-content">
        <h2>Appointment History for {{ $patient->name }}</h2>
        <a href="{{ route('admin.patients.index') }}" class="btn">← Back to Manage Patients</a>

        <table class="booking-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Doctor's Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                        <td>{{ $booking->doctor->user->name ?? 'Unknown' }}</td>
                        <td>{{ $booking->service }}</td>
                        <td>{{ ucfirst($booking->booking_status) }}</td>
                        <td>
                            @if($booking->doctor_note)
                                <button class="btn-note" onclick="showDoctorNoteModal(`{{ $booking->doctor_note }}`)">View</button>
                            @else
                                <em>No note</em>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No booking history found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
    {{ $bookings->links('pagination::simple-tailwind') }}
    </div>
</div>

<div id="doctorNoteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Doctor's Note</h3>
        <textarea id="doctorNoteText" readonly></textarea>
        <button class="btn btn-sm btn-success" onclick="closeDoctorNoteModal()">OK</button>
    </div>
</div>

<script>
    function showDoctorNoteModal(note) {
        document.getElementById('doctorNoteText').value = note;
        document.getElementById('doctorNoteModal').style.display = 'flex';
    }

    function closeDoctorNoteModal() {
        document.getElementById('doctorNoteModal').style.display = 'none';
    }
</script>

</body>
</html>
