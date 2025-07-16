<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Booking List</title>
    @vite('resources/css/admin.css')
</head>
<body>

<div class="admin-wrapper">
    <aside class="sidebar">
            <h2>Admin Panel</h2>
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
                    <li><a href="#" class="active">Manage Bookings</a></li>
                    <li><a href="{{ route('admin.doctors.index') }}">Manage Doctors</a></li>
                    <li><a href="{{ route('admin.patients.index') }}">Manage Patients</a></li>
                    <li><a href="{{ route('admin.settings') }}">Settings</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        </aside> 
    <div class="admin-content">
        <h1>Bookings - Branch: {{ Auth::user()->branch->name ?? 'Unknown' }}</h1>



    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.bookings', ['branch_id' => auth()->user()->branch_id]) }}">
        <div class="filters">
        <label>
            From:
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </label>
        <label>
            To:
            <input type="date" name="end_date" value="{{ request('end_date') }}">
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

            <label>
                Status:
                <select name="status" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="reschedule_pending" {{ request('status') === 'reschedule_pending' ? 'selected' : '' }}>Reschedule Pending</option>
                </select>
            </label>

            <label>
                Patient:
                <input type="text" name="patient" placeholder="Search by name"
                       value="{{ request('patient') }}" onblur="this.form.submit()">
            </label>
            <button type="submit">Filter</button>
    </form>

    <!-- Booking Table -->
    <div class="table-responsive">
        <table class="booking-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Doctor's Note</th> <!-- ✅ Add this -->
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                        <td>{{ $booking->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $booking->service }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $booking->booking_status)) }}</td>
                        <td>
                            @if ($booking->payment_status === 'paid_in_full')
                                <span class="badge badge-success">Paid</span>
                            @elseif ($booking->payment_status === 'deposit_paid')
                                <span class="badge badge-warning">Deposit Paid</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($booking->payment_status ?? 'N/A') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($booking->doctor_note)
                                <button class="btn-note" onclick="showDoctorNoteModal(`{{ $booking->doctor_note }}`)">View</button>
                            @else
                                <em>No note</em>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
            <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
                {{ $bookings->withQueryString()->links('pagination::simple-tailwind') }}
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
