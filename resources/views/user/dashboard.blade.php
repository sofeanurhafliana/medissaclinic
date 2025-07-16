<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Dental Clinic</title>
    @vite('resources/css/dashboard.css')
</head>
<body>
<div id="cancelModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:white; padding:20px; max-width:500px; margin:100px auto; border-radius:10px; position:relative;">
        <h3>Are you sure you want to cancel this booking?</h3>
        <form id="cancelForm" action="{{ route('user.booking.cancel') }}" method="POST">
            @csrf
            <input type="hidden" name="booking_id" id="cancelBookingId">
            <div class="form-group">
                <label for="reason">Reason for cancellation:</label>
                <textarea name="reason" id="cancelReason" required style="width:100%; height:80px;"></textarea>
            </div>
            <p style="color: #6c757d;">Deposit will not be refunded.</p>
            <div style="margin-top:10px;">
                <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Close</button>
            </div>
        </form>
    </div>
</div>
    <div class="user-wrapper">
        <!-- Sidebar -->
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
                    <li><a href="#" class="active">Dashboard</a></li>
                    <li><a href="{{ route('user.booking.create') }}">Make a booking</a></li>
                    <li><a href="{{route('user.manage.booking')}}">Manage Booking</a></li>
                    <li><a href="{{ route('user.settings') }}">Settings</a></li>
                    <li>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->

        <main class="dashboard-content">
            @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
            <header class="dashboard-header">
                <h1>Welcome, User {{ Auth::user()->name }}</h1>
                <p>Today is {{ now()->format('l, F j, Y') }}</p>
            </header>


            <!-- Latest Bookings Table -->
            <section class="latest-bookings">
                <h2>Recent Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Branch</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th> <!-- New column -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentBookings ?? [] as $booking)
                        <tr>
                            <td>{{ $booking->service }}</td>
                            <td>{{ $booking->branchInfo->name ?? 'N/A' }}</td>
                            <td>{{ $booking->doctor->name ?? 'N/A' }}</td>
                            <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') : 'N/A' }}</td>
                            <td>{{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('H:i') : 'N/A' }}</td>
                            <td>
                                {{ $booking->booking_status === 'rescheduled' ? 'Rescheduled' : ucwords(str_replace('_', ' ', $booking->booking_status)) }}
                            </td>
                                <td>
                                    @if ($booking->payment_status === 'paid_in_full')
                                        Paid
                                    @elseif ($booking->payment_status === 'deposit_paid')
                                        Deposit Paid
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                    @endif
                                </td>

                            <td>
                                <div class="action-buttons">
                                @if ($booking->booking_status === 'rescheduled')
                                    <form action="{{ route('user.booking.response', $booking->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="response" value="accepted">
                                        <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                    </form>
                                    <form action="{{ route('user.booking.response', $booking->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="response" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                @elseif ($booking->booking_status === 'approved')
                                    <button class="btn btn-sm btn-warning" onclick="showCancelModal({{ $booking->id }})">Cancel Booking</button>
                                @else
                                    -
                                @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8">No recent bookings</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
                {{ $recentBookings->withQueryString()->links('pagination::simple-tailwind') }}
            </div>
        </main>
            <script>
                function showCancelModal(bookingId) {
                    document.getElementById('cancelBookingId').value = bookingId;
                    document.getElementById('cancelModal').style.display = 'block';
                }

                function closeCancelModal() {
                    document.getElementById('cancelModal').style.display = 'none';
                }
            </script>

    </div>
</body>
</html>