
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
                    <li><a href="#" class="active">Dashboard</a></li>
                    <li><a href="{{ route('admin.bookings', ['branch_id' => auth()->user()->branch_id]) }}">Manage Bookings</a></li>
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

        <!-- Main Content -->
        <main class="dashboard-content">
            <header class="dashboard-header">
                <h1>Welcome, Admin {{ Auth::user()->name }}</h1>
                <p>Branch: {{ Auth::user()->branch->name ?? 'Not assigned' }}</p>
                <p>Today is {{ now()->format('l, F j, Y') }}</p>
            </header>

            <!-- Dashboard Widgets -->
            <section class="dashboard-widgets">
                <div class="widget">
                    <h3>Total Bookings This Month</h3>
                    <p>{{ $totalBookingsThisMonth ?? 0 }}</p>
                </div>
                <div class="widget">
                    <h3>Total Patients</h3>
                    <p>{{ $totalPatients ?? 0 }}</p>
                </div>

                <div class="widget">
                    <h3>Total Doctors</h3>
                    <p>{{ $totalDoctors ?? 0 }}</p>
                </div>
            </section>

            <!-- Latest Bookings Table -->
            <section class="latest-bookings">
                <h2>Recent Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                <td>{{ $booking->doctor->name ?? 'N/A' }}</td>
                                <td>{{ $booking->service }}</td>
                                <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') : 'N/A' }}</td>
                                <td>{{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('H:i') : 'N/A' }}</td>
                                <td>{{ ucfirst($booking->booking_status) }}</td>
                                <td>
                                    @if ($booking->payment_status === 'paid_in_full')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif ($booking->payment_status === 'deposit_paid')
                                        <span class="badge badge-warning">Deposit Paid</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($booking->payment_status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No recent bookings</td></tr>
                        @endforelse
                    </tbody>
                </table>
                    <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
                        {{ $bookings->withQueryString()->links('pagination::simple-tailwind') }}
                    </div>


                {{-- Pagination links go here, outside the table --}}

            <h2>🛑 Bookings Needing Reschedule</h2>
            <table class="table-auto w-full border border-gray-300 mb-8">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Patient</th>
                        <th class="px-4 py-2">Doctor</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Time</th>
                        <th class="px-4 py-2">Service</th>
                        <th class="px-4 py-2">Payment</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rescheduleBookings as $booking)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $booking->user->name }}</td>
                            <td class="px-4 py-2">{{ $booking->doctor->user->name }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i')}}</td>
                            <td class="px-4 py-2">{{ $booking->service }}</td>
                                <td class="px-4 py-2">
                                    @if ($booking->payment_status === 'paid_in_full')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif ($booking->payment_status === 'deposit_paid')
                                        <span class="badge badge-warning">Deposit Paid</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($booking->payment_status) }}</span>
                                    @endif
                                </td>
                            <td class="px-4 py-2">
                            <a href="{{ route('admin.bookings.reschedule', $booking->id) }}" class="btn btn-warning btn-sm">
                                Reschedule
                            </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">✅ No bookings need reschedule.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
                    <div class="pagination-wrapper" style="text-align:center; margin-top:20px;">
                        {{ $rescheduleBookings->withQueryString()->links('pagination::simple-tailwind') }}
                    </div>
            </section>
        </main>
    </div>
</body>
</html>
