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
    <h1>Edit Doctor</h1>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
    <div class="alert" style="background-color: #fdecea; color: #c0392b; border-color: #e74c3c;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


    <form action="{{ route('admin.doctors.update', $doctor->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <input type="text" name="name" value="{{ old('name', $doctor->user->name) }}" placeholder="Doctor Name" required class="form-control">
        </div>

        <div class="form-group">
            <input type="email" name="email" value="{{ old('email', $doctor->user->email) }}" placeholder="Doctor Email" required class="form-control">
        </div>

        @if($doctor->user->profile_picture)
            <div class="form-group">
                <label>Current Profile Picture:</label><br>
                <img src="{{ asset('images/' . $doctor->user->profile_picture) }}" alt="Doctor Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
            </div>
        @endif

        <div class="form-group">
            <label for="profile_picture">Upload New Profile Picture</label>
            <input type="file" name="profile_picture" accept="image/*" class="form-control">
        </div>

        <button type="submit" class="btn" style="width: 100%;">Update Doctor</button>
    </form>
</div>