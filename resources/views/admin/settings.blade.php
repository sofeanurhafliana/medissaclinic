<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dental Clinic</title>
    @vite('resources/css/admin.css')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Clinic Admin</h2>
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
                    <li><a href="{{ route('admin.patients.index') }}">Manage Patients</a></li>
                    <li><a href="#"class="active">Settings</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

<div class="admin-content">
    <h2>Branch Information</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Branch Info Box --}}
    <div id="branchInfoBox" style="
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        background-color: #fefefe;
        margin-bottom: 30px;
        line-height: 1.8;
        max-width: 600px;
    ">
        <strong>Branch Name:</strong> {{ $branch->name }}<br>
        <strong>Address:</strong> {{ $branch->address }}<br>
        <strong>Google Maps Location:</strong> {{ $branch->google_maps_link }}<br>
        <strong>Phone:</strong> {{ $branch->phone ?? 'N/A' }}<br>
        <strong>Status:</strong> {{ $branch->active ? 'Active' : 'Inactive' }}
    </div>

    <form id="branchForm" action="{{ route('admin.settings.update') }}" method="POST" style="max-width: 600px; display: none;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Branch Name</label>
            <input type="text" name="name" value="{{ $branch->name }}" required>
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="{{ $branch->address }}" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ $branch->phone }}">
        </div>

        <div class="form-group">
            <label>Google Maps Location</label>
            <input type="text" name="googlemapslink" value="{{ $branch->google_maps_link }}">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="active">
                <option value="1" {{ $branch->active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$branch->active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
    </form>

    {{-- Button to edit branch (if needed) --}}
    <button id="editBranchBtn" class="btn btn-warning" style="margin-bottom: 40px;">Edit Branch Info</button>

    <h2>Admin Information</h2>

    {{-- Admin Info Box --}}
    <div id="adminInfoBox" style="
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

    {{-- Admin Edit Form --}}
    <form id="adminForm" action="{{ route('admin.update.profile') }}" method="POST" enctype="multipart/form-data" style="max-width: 600px; display: none;">
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

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="cancelAdminEdit()">Cancel</button>
    </form>

    <button id="editAdminBtn" class="btn btn-warning" style="margin-top: 10px;">Edit Admin Info</button>


<div style="font-size: 0.9em; color: #6c757d;">
    <p>If you encounter any issues with the system, please contact our IT support team at:</p>
    <p style="margin: 2px 0;">📞 +60 17-8540718 (Sofea)</p>
    <p style="margin: 2px 0;">📞 +60 19-7567138 (Nisa)</p>
    <p style="margin: 2px 0;">📞 +60 11-63652712 (Zulaikha)</p>
    <p style="margin: 2px 0;">📧 support@medissa.clinic.com</p>
</div>


</div>


<script>
    // Branch toggle
    document.getElementById('editBranchBtn')?.addEventListener('click', function () {
        document.getElementById('branchInfoBox').style.display = 'none';
        document.getElementById('branchForm').style.display = 'block';
        this.style.display = 'none';
    });

    // Admin toggle
    document.getElementById('editAdminBtn')?.addEventListener('click', function () {
        document.getElementById('adminInfoBox').style.display = 'none';
        document.getElementById('adminForm').style.display = 'block';
        this.style.display = 'none';
    });

    function cancelAdminEdit() {
        document.getElementById('adminForm').style.display = 'none';
        document.getElementById('adminInfoBox').style.display = 'block';
        document.getElementById('editAdminBtn').style.display = 'inline-block';
    }

    function cancelEdit() {
        document.getElementById('branchForm').style.display = 'none';
        document.getElementById('branchInfoBox').style.display = 'block';
        document.getElementById('editBranchBtn').style.display = 'inline-block';
    }

</script>


</div>
