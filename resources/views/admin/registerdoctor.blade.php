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
                <li><a href="/admin/doctors">Doctors</a></li>
            </ul>
        </nav>
    </div> 

    <div class="admin-content">
        <h1>Register New Doctor</h1>

        @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

        <form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 1rem;">
                <input type="text" name="name" placeholder="Doctor Name" required class="form-control" />
            </div>
            <div style="margin-bottom: 1rem;">
                <input type="email" name="email" placeholder="Doctor Email" required class="form-control" />
            </div>
            <div style="margin-bottom: 1.5rem;">
                <input type="password" name="password" placeholder="Password" required class="form-control" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="profile_picture">Upload Profile Picture (optional)</label>
                <input type="file" name="profile_picture" accept="image/*" class="form-control">
            </div>

            <div class="button-row doctor-management">
                <button type="submit" class="btn btn-register">Register Doctor</button>
            </div>
        </form>
    </div>
</div>


{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Doctors</title>
    @vite('resources/css/admin.css')
</head>
<body>

<div class="admin-container">
    <h1>Register New Doctor</h1>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.doctors.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <input type="text" name="name" placeholder="Doctor Name" required class="form-control">
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="Doctor Email" required class="form-control">
        </div>

        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required class="form-control">
        </div>

        <button type="submit" class="btn">Register Doctor</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-dashboard">
            Back to Dashboard
        </a>
    </form>
</div> --}}
