<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    @vite('resources/css/doctor.css')
</head>
<body>
    <div class="doctor-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Profile Settings</h2>
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
                    <li><a href="{{ route('doctor.availability.view') }}">Availability</a></li>
                    <li><a href= "{{route('doctor.manage.bookings')}}">Manage Bookings</a></li>
                    <li><a href="#" class="active">Settings</a></li>
                    <li>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>

        </aside>
    <div class="dashboard-content">
        <h2>Account Settings</h2>

    <div class="user-content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Read-only Info Box --}}
        <div id="userInfoBox" style="
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            background-color: #fefefe;
            margin-bottom: 20px;
            line-height: 1.8;
            max-width: 600px;
        ">
            <strong>Name:</strong> {{ auth()->user()->name }}<br>
            <strong>Email:</strong> {{ auth()->user()->email }}
        </div>

        {{-- Edit Form (hidden initially) --}}
        <form id="userEditForm" action="{{ route('doctor.settings.update') }}" method="POST" style="max-width: 600px; display: none;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ auth()->user()->name }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ auth()->user()->email }}" required>
            </div>

            <hr>
            <h4>Change Password (optional)</h4>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password">
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="new_password_confirmation">
            </div>

            <div class="button-group" style="margin-top: 15px;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
            </div>
        </form>

        {{-- Edit Toggle Button --}}
        <button id="editBtn" class="btn btn-warning" style="margin-top: 10px;">Edit</button>
    </div>
    <div style="font-size: 0.9em; color: #6c757d;">
    <p>If you encounter any issues with the system, please contact our IT support team at:</p>
    <p style="margin: 2px 0;">📞 +60 17-8540718 (Sofea)</p>
    <p style="margin: 2px 0;">📞 +60 19-7567138 (Nisa)</p>
    <p style="margin: 2px 0;">📞 +60 11-63652712 (Zulaikha)</p>
    <p style="margin: 2px 0;">📧 support@medissa.clinic.com</p>
</div>
</div>

        <script>
            const editBtn = document.getElementById('editBtn');
            const userInfoBox = document.getElementById('userInfoBox');
            const userEditForm = document.getElementById('userEditForm');

            editBtn.addEventListener('click', function () {
                userInfoBox.style.display = 'none';
                userEditForm.style.display = 'block';
                editBtn.style.display = 'none';
            });

            function cancelEdit() {
                userInfoBox.style.display = 'block';
                userEditForm.style.display = 'none';
                editBtn.style.display = 'inline-block';
            }
        </script>


</div>

    </div>
</div>
</body>
</html>
