<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    @vite('resources/css/dashboard.css')
</head>
<body>
<div class="user-wrapper">
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
                    <li><a href="{{ route('user.booking.create') }}">Make a booking</a></li>
                    <li><a href="{{route('user.manage.booking')}}">Manage Booking</a></li>
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
    <form id="userEditForm" action="{{ route('user.update.profile') }}" method="POST" enctype="multipart/form-data" style="max-width: 600px; display: none;">
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

        <div class="form-group">
            <label>Profile Picture</label><br>
            @if(auth()->user()->profile_picture)
                <img src="{{ asset('images/' . auth()->user()->profile_picture) }}" alt="Profile Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
            @endif
            <input type="file" name="profile_picture" accept="image/*">
            <small>Leave blank to keep current photo.</small>
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

        <div class="button-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" onclick="cancelUserEdit()">Cancel</button>

        </div>

    </form>

    {{-- Toggle Button --}}
            <div class="button-group">
    <button id="editUserBtn" class="btn btn-warning" style="margin-top: 10px;">Edit Profile</button>
            </div>
        {{-- Soft Delete Account --}}
        <form action="{{ route('user.softdelete') }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
            @csrf
            @method('DELETE')
            <div class="button-group">
            <button type="submit" class="btn btn-danger">Deactivate Account</button>
            </div>
        </form>
          <hr>

    <div style="font-size: 0.9em; color: #6c757d;">
    <p>If you encounter any issues,  please contact our team at:</p>
    <p style="margin: 2px 0;">📞 +60 13-7071644 (Ina)</p>
    <p style="margin: 2px 0;">📞 +60 10-4303380 (Insyi)</p>
    <p style="margin: 2px 0;">📧 customer@medissa.clinic.com</p>
    </div>

        <script>
            document.getElementById('editUserBtn').addEventListener('click', function () {
                document.getElementById('userInfoBox').style.display = 'none';
                document.getElementById('userEditForm').style.display = 'block';
                this.style.display = 'none';
            });

            function cancelUserEdit() {
                document.getElementById('userEditForm').style.display = 'none';
                document.getElementById('userInfoBox').style.display = 'block';
                document.getElementById('editUserBtn').style.display = 'inline-block';
            }
        </script>

    </div>
</div>
</body>
</html>
