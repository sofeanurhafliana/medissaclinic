<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class DoctorController extends Controller
{
    /**
     * Show doctor's dashboard with recurring weekly availability.
     */
    public function index()
{
    
    $doctor = Doctor::where('user_id', Auth::id())->first();
    $bookings = $doctor ? $doctor->bookings()->with('user')->get() : collect();

    $bookings = Booking::where('doctor_id', $doctor->id)
        ->whereDate('booking_date', '>=', now()->toDateString())
        ->orderBy('booking_date')
        ->get();
    
    

    return view('doctor.dashboard', compact('bookings'));
}

    /**
     * Update weekly availability schedule (recurring).
     */
    public function updateAvailability(Request $request)
    {
        foreach ($request->schedule as $day => $times) {
            DoctorSchedule::updateOrCreate(
                ['doctor_id' => auth()->id(), 'day_of_week' => $day],
                [
                    'start_time' => $times['start'],
                    'end_time' => $times['end'],
                    'available' => true
                ]
            );
        }
        

        return redirect()->back()->with('success', 'Schedule updated.');
    }

    /**
     * View doctor's current booking schedule.
     */
    public function viewSchedule(Request $request)
    {
    $userId = Auth::id();

    // Get the doctor linked to this user
    $doctor = Doctor::where('user_id', $userId)->first();

    if (!$doctor) {
        abort(403, 'Doctor profile not found.');
    }

    $query = Booking::with('user')
        ->where('doctor_id', $doctor->id)
        ->orderBy('booking_date')
        ->where('booking_status', '!=', 'cancelled');

        // Filter by time view
    $startTime = '09:00:00';
    $endTime = '17:00:00';

    // Filter by time view
    if ($request->view === 'day') {
        $query->whereDate('booking_date', Carbon::today())
              ->whereTime('booking_time', '>=', $startTime)
              ->whereTime('booking_time', '<=', $endTime);
    } elseif ($request->view === 'week') {
        $query->whereBetween('booking_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
              ->whereTime('booking_time', '>=', $startTime)
              ->whereTime('booking_time', '<=', $endTime);
    } elseif ($request->view === 'month') {
        $query->whereMonth('booking_date', Carbon::now()->month);
        // No time restriction for monthly view (optional: add if needed)
    }

        $bookings = $query->get();
        $unavailabilityQuery = DoctorUnavailability::where('doctor_id', $doctor->id);

        if ($request->view === 'day') {
            $unavailabilityQuery->whereDate('unavailable_date', Carbon::today());
        } elseif ($request->view === 'week') {
            $unavailabilityQuery->whereBetween('unavailable_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($request->view === 'month') {
            $unavailabilityQuery->whereMonth('unavailable_date', Carbon::now()->month);
        }

        $unavailabilities = $unavailabilityQuery->get();
        $holidays = PublicHoliday::get(['date', 'name']); // instead of just pluck('date')
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

    return view('doctor.schedule', compact('bookings', 'unavailabilities', 'holidays', 'services'));
    }
    

    /**
     * Store new unavailability and check for conflicting bookings.
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'unavailable_date' => 'required|date|after_or_equal:today',
            'unavailable_start' => 'required|date_format:H:i',
            'unavailable_end' => 'required|date_format:H:i|after:unavailable_start',
            'note' => 'nullable|string'
        ]);
        if (
            $request->unavailable_start < '09:00' ||
            $request->unavailable_end > '17:00'
        ) {
            return back()->withErrors(['Time must be between 09:00 and 17:00.']);
        }
        

            $date = Carbon::parse($request->unavailable_date);



        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor) {
            abort(403, 'Doctor profile not found.');
        }

        $doctorId = $doctor->id;

        // Check for conflicting bookings
        $conflicts = Booking::where('doctor_id', $doctorId)
            ->where('booking_date', $request->unavailable_date)
            ->whereTime('booking_time', '>=', $request->unavailable_start)
            ->whereTime('booking_time', '<', $request->unavailable_end)
            ->where('booking_status', 'approved')
            ->with('user')
            ->get();

        if ($conflicts->count() > 0) {
            // Save data temporarily in session for confirmation
            session([
                'conflict_bookings' => $conflicts,
                'unavailability_data' => $request->all()
            ]);

            return redirect()->route('doctor.schedule.confirm');
        }

        // No conflict — save directly
        DoctorUnavailability::create([
            'doctor_id' => $doctorId,
            'unavailable_date' => $request->unavailable_date,
            'unavailable_start' => $request->unavailable_start,
            'unavailable_end' => $request->unavailable_end,
            'note' => $request->note,
        ]);
        

        return redirect()->route('doctor.availability.view')->with('status', 'Unavailability saved successfully.');
    }

    /**
     * Show conflict confirmation screen.
     */
    public function confirmUnavailability()
    {
        $conflicts = session('conflict_bookings');
        return view('doctor.confirm-unavailability', compact('conflicts'));
    }

    /**
     * Finalize saving unavailability and cancel conflicting bookings.
     */
    public function finalizeUnavailability(Request $request)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor) {
            abort(403, 'Doctor profile not found.');
        }

        $doctorId = $doctor->id;
        $data = session('unavailability_data');
        $conflicts = session('conflict_bookings');

        // Save the unavailability
        DoctorUnavailability::create([
            'doctor_id' => $doctorId,
            'unavailable_date' => $data['unavailable_date'],
            'unavailable_start' => $data['unavailable_start'],
            'unavailable_end' => $data['unavailable_end'],
            'note' => $data['note'] ?? null,
        ]);

        // Cancel all conflicting bookings
        foreach ($conflicts as $bookingData) {
            $booking = \App\Models\Booking::with(['user', 'doctor.user', 'branchInfo.admin'])->find($bookingData['id']);

            if ($booking) {
                $booking->update([
                    'booking_status' => 'reschedule_pending',
                    'reschedule_reason' => 'Doctor marked unavailable on this date.',
                    'reschedule_requested_by' => 'doctor'
                ]);

                $adminEmail = $booking->branchInfo->admin?->email ?? null;

                if ($adminEmail) {
                    Mail::to($adminEmail)->send(new \App\Mail\BookingPendingReschedule($booking));
                } else {
                    \Log::warning('No admin email found for branch ID ' . $booking->branch_id);
                }

                return redirect()->route('doctor.schedule.view')->with('status', 'Unavailability submitted and admin notified.');

            }
        }
}


    /**
     * View doctor's unavailability records.
     */
    public function viewAvailability()
    {
        
    $doctor = Doctor::where('user_id', Auth::id())->first();

    if (!$doctor) {
        abort(403, 'Doctor profile not found.');
    }

        $doctorId = $doctor->id;
        $bookings = Booking::where('doctor_id', $doctor->id)->get();


        $unavailabilities = DoctorUnavailability::where('doctor_id', $doctorId)
            ->orderBy('unavailable_date', 'asc')
            ->paginate(5);

            $holidays = PublicHoliday::pluck('date')->toArray();
            return view('doctor.availability', compact('unavailabilities', 'holidays'));
    }

    public function settings()
    {
    return view('doctor.settings', ['doctor' => auth()->user()]);
}

public function updateProfile(Request $request)
{
    $doctor = auth()->user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $doctor->id,
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8|confirmed',
    ]);

    $doctor->name = $request->name;
    $doctor->email = $request->email;

    if ($request->filled('current_password') && $request->filled('new_password')) {
        if (!Hash::check($request->current_password, $doctor->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $doctor->password = Hash::make($request->new_password);
    }

    $doctor->save();

    return back()->with('success', 'Profile updated successfully.');
}

public function manageBookings(Request $request)
{
    $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

    $query = Booking::where('doctor_id', $doctor->id)->with('user');

    // Filter past bookings
    $query->where('booking_date', '<', now()->toDateString());

    // Optional filters
    if ($request->filled('start_date')) {
        $query->whereDate('booking_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('booking_date', '<=', $request->end_date);
    }

    $bookings = $query->orderBy('booking_date')->paginate(3);

    return view('doctor.manage_bookings', compact('bookings'));
}

public function updateBookingNote(Request $request, Booking $booking)
{
    $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

    if ($booking->doctor_id !== $doctor->id || $booking->booking_date >= now()->toDateString()) {
        abort(403, 'Unauthorized to edit this booking.');
    }

    $request->validate([
        'note' => 'nullable|string|max:2000',
    ]);

    $booking->doctor_note = $request->doctor_note;
    $booking->save();

    return back()->with('status', 'Note updated successfully.');
}


}