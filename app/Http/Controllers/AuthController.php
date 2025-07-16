<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

        public function index()
        {
            $branches = Branch::where('active', true)->get(); // Optional: show only active branches
            return view('welcome', compact('branches'));
        }

    
    /**
     * Show the registration form
     */
    public function showForm(): View
    {
        $branches = Branch::all(); // Pass branches for admin registration
        return view('auth.register', compact('branches'));
    }

    /**
     * Handle user registration
     */
    public function processForm(Request $request): RedirectResponse
    {
        // Compatible for PHP < 8 (alternative to str_ends_with)
        $email = $request->email;
        $isAdminEmail = str_ends_with($request->email, '@admin.medissa.com');

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];

        if ($isAdminEmail) {
            $rules['branch_id'] = 'required|exists:branches,id';
        }

        $request->validate($rules);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $isAdminEmail ? 'admin' : 'user',
            'branch_id' => $isAdminEmail ? $request->branch_id : null,
        ]);

        return redirect()->route('login')->with('status', 'Registration successful. You can now log in.');
    }

    /**
     * Show the login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request): RedirectResponse
    {
        User::where('email', $request->email)->whereNull('deleted_at')->first();
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'doctor':
                    return redirect()->route('dashboard.doctor');
                default:
                    return redirect()->route('dashboard.user');
            }
        }

        return back()->withErrors([
            'login_error' => 'Wrong email or password!',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Logout successful');
    }

    /**
     * User Dashboard
     */
    public function userDashboard(): View
    {
        $userId = auth()->id();
        
        Booking::where('user_id', $userId)
            ->whereDate('booking_date', '<', today())
            ->where('payment_status', '!=', 'paid_in_full')
            ->where('booking_status', '!=', 'cancelled')
            ->update(['payment_status' => 'paid_in_full']);

        $recentBookings = Booking::where('user_id', $userId)
        ->where('booking_status', '!=', 'cancelled')
        ->orderByRaw("CASE 
            WHEN booking_status = 'rescheduled' THEN 0 
            ELSE 1 
            END")
        ->orderBy('booking_date', 'asc') // earliest date first
        ->orderBy('booking_time', 'asc')
        ->paginate(4);

        return view('user.dashboard', compact('recentBookings'));
    }

public function updateProfile(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8|confirmed',
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

    // Only update password if current and new password provided
    if ($request->filled('current_password') && $request->filled('new_password')) {
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
    }

    $user->save();

    return back()->with('success', 'Profile updated successfully!');
}

public function softDeleteAccount()
{
    $user = Auth::user();
    $user->delete();

    Auth::logout();

    return redirect('/')->with('status', 'Your account has been deactivated.');
}


    /**
     * Admin Dashboard
     */
    public function adminDashboard(): View
    {
        $branchId = auth()->user()->branch_id;

        $doctors = \App\Models\Doctor::where('branch_id', $branchId)->get();
        $bookings = Booking::where('branch_id', $branchId)->latest()->take(10)->get();

        return view('admin.dashboard', compact('doctors', 'bookings'));
    }

    /**
     * Doctor Dashboard
     */
        public function doctorDashboard(): View
        {
            $userId = auth()->id();

            $doctor = \App\Models\Doctor::where('user_id', $userId)->first();

            if (!$doctor) {
                abort(403, 'Doctor profile not found.');
            }

            $bookings = \App\Models\Booking::with('user') // eager load user relationship
                ->where('doctor_id', $doctor->id)
                ->where('booking_status', '!=', 'cancelled') // exclude cancelled
                ->whereDate('booking_date', '>=', now()->toDateString()) // optional: upcoming only
                ->orderBy('booking_date', 'asc') // earliest date first
                ->orderBy('booking_time', 'asc')
                ->paginate(4);

            $allBookingsThisMonth = \App\Models\Booking::with('user')
                ->where('doctor_id', $doctor->id)
                ->where('booking_status', '!=', 'cancelled')
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->get();

            $allBookings = \App\Models\Booking::with('user')
                ->where('doctor_id', $doctor->id)
                ->where('booking_status', '!=', 'cancelled')
                ->orderBy('booking_date', 'asc')
                ->orderBy('booking_time', 'asc')
                ->get();

            $services = [
                "Braces" => 60,
                "Whitening" => 60,
                "Scaling & Polishing" => 30,
                "Extraction" => 30,
                "Crown & Bridge" => 60,
                "Veneers" => 60,
                "Implant" => 60,
                "Root Canal Treatment" => 60,
                "Filling" => 30,
                "Denture" => 60,
                "Minor Oral Surgery" => 60,
                "Radiology" => 30,
                "Kids Treatment" => 30,
                "Dental Checkup" => 30,
            ];

            return view('doctor.dashboard', compact('bookings', 'services', 'allBookingsThisMonth', 'allBookings'));
        }

    }