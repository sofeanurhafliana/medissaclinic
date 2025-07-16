<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Patients</title>
    @vite('resources/css/admin.css')
</head>
<body>

<div class="admin-wrapper">
    <div class="sidebar">
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
                    <li><a href="{{ route('admin.bookings', ['branch_id' => auth()->user()->branch_id]) }}">Manage Bookings</a></li>
                    <li><a href="{{ route('admin.doctors.index') }}">Manage Doctors</a></li>
                    <li><a href="#" class="active">Manage Patients</a></li>
                    <li><a href="{{ route('admin.settings') }}">Settings</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
            </ul>
        </nav>
    </div> 

    <div class="admin-content">
        <h2 class="section-title">Patient Accounts</h2>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="table-responsive">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Appointments</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $index => $patient)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $patient->name }}</td>
                            <td>{{ $patient->email }}</td>
                            <td>
                                <span>
                                    {{ $patient->deleted_at ? 'Inactive' : 'Active' }}
                                </span>
                            </td>
                            <td>
                                <a href="mailto:{{ $patient->email }}" class="btn btn-contact">Email</a>
                            </td>
                            <td>
                                <a href="{{ route('admin.patients.history', $patient->id) }}" class="btn btn-view">See Past History</a>
                            </td>
                            <td>
                                <form action="{{ $patient->deleted_at 
                                    ? route('admin.patients.restore', $patient->id) 
                                    : route('admin.patients.deactivate', $patient->id) }}" method="POST">
                                    @csrf
                                    @if(!$patient->deleted_at)
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-sm">Reactivate</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No patient accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
