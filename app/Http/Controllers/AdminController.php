<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\DoctorUnavailability;
use App\Models\PublicHoliday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\BookingRescheduledNotification;
use Illuminate\Support\Facades\Mail;


class AdminController extends Controller
{
    public function index()
{
    $branchId = Auth::user()->branch_id;

    // Total bookings for this branch
    $totalBookings = Booking::where('branch_id', $branchId)->count();
    $totalBookingsThisMonth = Booking::where('branch_id', $branchId)
        ->whereMonth('booking_date', now()->month)
        ->whereYear('booking_date', now()->year)
        ->where('booking_status', '!=', 'cancelled') // optional filter
        ->count();

    // Total patients for this branch
    $totalPatients = User::whereHas('bookings', function ($query) use ($branchId) {
        $query->where('branch_id', $branchId);
    })->count();

    $totalDoctors = Doctor::where('branch_id', $branchId)->count();

    // Recent bookings for this branch with user and doctor eager loaded
$bookings = Booking::with(['doctor.user', 'user'])
    ->where('branch_id', $branchId)
    ->whereNotIn('booking_status', ['cancelled', 'reschedule_pending'])
    ->whereDate('booking_date', '>=', Carbon::today())
    ->orderBy('booking_date', 'asc')
    ->orderBy('booking_time', 'asc')
    ->paginate(4, ['*'], 'bookings_page'); // <- custom page name

$rescheduleBookings = Booking::with(['doctor.user', 'user'])
    ->where('branch_id', $branchId)
    ->where('booking_status', 'reschedule_pending')
    ->orderBy('booking_date', 'asc')
    ->orderBy('booking_time', 'asc')
    ->paginate(4, ['*'], 'reschedule_page'); // <- different page name

    

    return view('admin.dashboard', compact(
        'totalBookings',
        'totalBookingsThisMonth',
        'totalPatients',
        'totalDoctors',
        'bookings',
        'rescheduleBookings',));
}

    public function manageBookings(Request $request, $branch_id)
    {
        $adminBranch = auth()->user()->branch_id;

        if ($branch_id != $adminBranch) {
            abort(403, 'Unauthorized access to another branch.');
        }

        $query = Booking::with(['user', 'doctor', 'branchInfo'])->where('branch_id', $adminBranch);

        // Apply filters
        if ($request->date_filter === 'today') {
            $query->whereDate('booking_date', now());
        } elseif ($request->date_filter === 'week') {
            $query->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($request->date_filter === 'month') {
            $query->whereMonth('booking_date', now()->month);
        }

        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('patient')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->patient . '%'));
        }

        if ($request->status === 'needs_reschedule') {
        $query->where('booking_status', 'needs_reschedule');
    }

    if ($request->filled('status')) {
    $query->where('booking_status', $request->status);
}

// Custom date range filter
if ($request->filled('start_date') && $request->filled('end_date')) {
    $query->whereBetween('booking_date', [$request->start_date, $request->end_date]);
} elseif ($request->filled('start_date')) {
    $query->whereDate('booking_date', '>=', $request->start_date);
} elseif ($request->filled('end_date')) {
    $query->whereDate('booking_date', '<=', $request->end_date);
}


        $bookings = $query
            ->orderByRaw("
                CASE 
                    WHEN booking_status = 'reschedule_pending' THEN 0
                    WHEN booking_status = 'approved' THEN 1
                    WHEN booking_status = 'cancelled' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->paginate(10);

        $services = Booking::select('service')->distinct()->pluck('service');
        $doctors = Doctor::where('branch_id', $adminBranch)->get();

        return view('admin.bookings', compact('bookings', 'services', 'doctors'));
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
        'booking_status' => 'required|in:pending,approved,cancelled,reschedule_pending,rescheduled',
        'booking_date' => 'nullable|date',
        'booking_time' => 'nullable|date_format:H:i',
    ]);

    $booking = Booking::findOrFail($id);

    if ($request->booking_status === 'rescheduled') {
        // Check required fields
        if (!$request->booking_date || !$request->booking_time) {
            return back()->with('error', 'Please provide date and time for rescheduling.');
        }

        $newDate = Carbon::parse($request->booking_date);
        $newTime = $request->booking_time;

        // 1. Block Sundays
        if ($newDate->isSunday()) {
            return back()->with('error', 'Cannot reschedule on a Sunday.');
        }

        // 2. Block public holidays
        $holidays = PublicHoliday::pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))->toArray();

        if (in_array($newDate->toDateString(), $holidays)) {
            return back()->with('error', 'Selected date falls on a public holiday.');
        }


        // 3. Check for doctor conflicts
        $conflict = Booking::where('doctor_id', $booking->doctor_id)
            ->where('booking_date', $newDate->toDateString())
            ->where('booking_time', $newTime)
            ->where('id', '!=', $booking->id)
            ->whereNotIn('booking_status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Doctor already has a booking at this time.');
        }

        // No conflict, update booking
        $booking->booking_date = $newDate->toDateString();
        $booking->booking_time = $newTime;
    }

    $booking->booking_status = $request->booking_status;
    $booking->save();
    if ($request->booking_status === 'rescheduled') {
        Mail::to($booking->user->email)->send(new BookingRescheduledNotification($booking));
    }

    return redirect()->route('admin.dashboard')->with('status', 'Booking updated successfully.');
    }

    public function showRescheduleForm($id)
    {
        $booking = Booking::with('user', 'doctor')->findOrFail($id);
        $unavailabilities = DoctorUnavailability::with('doctor.user')
        ->get()
        ->map(function ($u) {
            return [
                'doctor_name' => $u->doctor->user->name,
                'start' => $u->unavailable_date . 'T' . $u->unavailable_start,
                'end' => $u->unavailable_date . 'T' . $u->unavailable_end,
                'note' => $u->note,
            ];
        });

    $holidays = PublicHoliday::pluck('date')->toArray();

    $bookings = Booking::with('doctor.user')
        ->where('branch_id', auth()->user()->branch_id)
        ->get()
        ->map(function ($b) {
            return [
                'doctor_name' => $b->doctor->user->name ?? 'Unknown',
                'service' => $b->service,
                'booking_date' => $b->booking_date,
                'booking_time' => $b->booking_time,
            ];
        });

    $doctors = Doctor::with('user')
        ->where('branch_id', auth()->user()->branch_id)
        ->get();


    return view('admin.reschedule-booking', compact('booking', 'holidays', 'bookings', 'doctors', 'unavailabilities'));
    }


    public function viewBookings(Request $request)
    {
        $adminBranch = Auth::user()->branch_id;

        $query = Booking::with(['doctor', 'user'])
            ->where('branch_id', $adminBranch);

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('service')) {
            $query->where('service', $request->service);
        }

        if ($request->has('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $query->whereBetween('booking_date', [$dates[0], $dates[1]]);
        }

        $bookings = $query->paginate(10);
        return view('admin.bookings', compact('bookings'));
    }

    public function create()
    {
        return view('admin.registerdoctor');
    }

    public function doctorsIndex()
{
    $adminBranchId = Auth::user()->branch_id;

    $doctors = Doctor::with('user')
        ->where('branch_id', $adminBranchId)
        ->get();

    return view('admin.doctors', compact('doctors'));
}

public function edit($id)
{
    $doctor = Doctor::with('user')->findOrFail($id);

    // Only allow access if the doctor belongs to the same branch
    if ($doctor->branch_id !== Auth::user()->branch_id) {
        abort(403);
    }

    return view('admin.editdoctor', compact('doctor'));
}

public function update(Request $request, $id)
{
    $doctor = Doctor::findOrFail($id);
    $user = $doctor->user;

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->hasFile('profile_picture')) {
        // Delete old picture if not default
        if ($user->profile_picture && $user->profile_picture !== 'profile_pictures/default.jpg') {
            $oldPath = public_path('images/' . $user->profile_picture);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $filename = time() . '.' . $request->profile_picture->extension();
        $request->profile_picture->move(public_path('images'), $filename);
        $user->profile_picture = $filename;
    }

    $user->save();
    return redirect()->route('admin.doctors.index')->with('success', 'Doctor updated successfully.');
}

public function destroy($id)
{
    $doctor = Doctor::findOrFail($id);

    if ($doctor->branch_id !== Auth::user()->branch_id) {
        abort(403);
    }

    $doctor->user()->delete(); // deletes from `users` table too if you use cascade
    $doctor->delete();

    return redirect()->back()->with('success', 'Doctor deleted.');
}

public function patientsIndex()
{
    $adminBranchId = auth()->user()->branch_id;

    $patients = User::withTrashed()
        ->where('role', 'user')
        ->whereHas('bookings', function ($query) use ($adminBranchId) {
            $query->where('branch_id', $adminBranchId);
        })
        ->with(['bookings' => function ($query) use ($adminBranchId) {
            $query->where('branch_id', $adminBranchId);
        }, 'bookings.doctor.user']) // eager load for history
        ->get();

    return view('admin.patients', compact('patients'));
}


public function viewPatientHistory($id)
{
    $adminBranchId = auth()->user()->branch_id;

    $patient = User::withTrashed()->findOrFail($id);

    // Check if the user has bookings at this branch
    $hasBooking = Booking::where('user_id', $id)
        ->where('branch_id', $adminBranchId)
        ->exists();

    if (!$hasBooking) {
        abort(403, 'Unauthorized to view this patient’s history.');
    }

    $bookings = Booking::with(['doctor.user', 'branchInfo'])
        ->where('user_id', $id)
        ->where('branch_id', $adminBranchId)
        ->orderBy('booking_date', 'desc')
        ->paginate(10);

    return view('admin.patients-history', compact('patient', 'bookings'));
}


public function deactivatePatient($id)
{
    $patient = User::where('role', 'user')->findOrFail($id);
    $patient->delete();
    return back()->with('success', 'Patient deactivated.');
}

public function restorePatient($id)
{
    $patient = User::withTrashed()->where('role', 'user')->findOrFail($id);
    $patient->restore();
    return back()->with('success', 'Patient reactivated.');
}

        public function updateProfile(Request $request)
        {
            $admin = auth()->user();

            $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $admin->id,
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:6|confirmed',
            ]);

            $admin->name = $request->name;
            $admin->email = $request->email;
            
            if ($request->hasFile('profile_picture')) {
                // Delete old picture if not default
                if ($admin->profile_picture && $admin->profile_picture !== 'profile_pictures/default.jpg') {
                    $oldPath = public_path('images/' . $admin->profile_picture);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $filename = time() . '.' . $request->profile_picture->extension();
                $request->profile_picture->move(public_path('images'), $filename);
                $admin->profile_picture = $filename;
            }

            if ($request->filled('new_password')) {
                if (!\Hash::check($request->current_password, $admin->password)) {
                    return back()->with('error', 'Current password is incorrect.');
                }

                $admin->password = \Hash::make($request->new_password);
            }

            $admin->save();

            return back()->with('success', 'Admin profile updated successfully.');
        }




    // Store new doctor and assign to admin's branch
public function store(Request $request)
{
    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $profilePictureName = null;

    // Handle profile picture upload if exists
    if ($request->hasFile('profile_picture')) {
        $profilePictureName = time() . '.' . $request->file('profile_picture')->extension();
        $request->file('profile_picture')->move(public_path('images'), $profilePictureName);
    }

    // Create doctor user
    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => Hash::make($validated['password']),
        'branch_id' => Auth::user()->branch_id,
        'role'     => 'doctor',
        'profile_picture' => $profilePictureName, // store filename
    ]);

    // Link to doctors table
    Doctor::create([
        'user_id' => $user->id,
        'branch_id' => Auth::user()->branch_id,
        'name' => $request->name,
        'email' => $user->email,
    ]);

    return redirect()->back()->with('success', 'Doctor registered successfully.');
}

        public function branchIndex()
{
    $branches = Branch::all();
    return view('admin.settings', compact('branches'));
}

public function settings()
{
    $admin = auth()->user();
    $branch = $admin->branch;

    return view('admin.settings', compact('admin', 'branch'));
}


public function updateBranch(Request $request)
{
    $admin = auth()->user();
    $branch = $admin->branch;

    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string',
        'phone' => 'nullable|string',
        'active' => 'boolean',
    ]);

    $branch->update($request->only(['name', 'address', 'phone', 'active']));

    return back()->with('success', 'Branch info updated.');
}

}
