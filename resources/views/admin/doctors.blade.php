 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Doctors</title>
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
                <li><a href="{{ route('admin.doctors.create') }}">Register Doctor</a></li>
            </ul>
        </nav>
    </div> 
    <div class="admin-content">
        <h2 class="section-title">Doctors working at this clinic</h2>

    @if(session('success'))
        <div class="alert">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($doctors as $index => $doctor)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $doctor->user->name }}</td>
                        <td>{{ $doctor->user->email }}</td>
                        <td>
                            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-edit">Edit</a>

                            <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this doctor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No doctors currently working at your branch.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>


{{-- <div class="admin-container doctor-management"> 
    <h1>Doctors Working at Your Clinic</h1>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="button-row">
        <a href="{{ route('admin.doctors.create') }}" class="btn btn-register">
            + Register Doctor
        </a>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-dashboard">
            Back to Dashboard
        </a>
    </div>


    <div class="table-responsive">
        <table class="booking-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($doctors as $index => $doctor)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $doctor->user->name }}</td>
                        <td>{{ $doctor->user->email }}</td>
                        <td>
                            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-edit">
                                Edit
                            </a>

                            <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this doctor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No doctors currently working at your branch.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>--}}

</body>
</html>