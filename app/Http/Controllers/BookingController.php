<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Branch;
use App\Models\PublicHoliday;
use App\Models\BookingCancellation;
use App\Models\DoctorUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Mail\BookingCancelledByUser;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\BookingNotificationDoctor;
use App\Mail\BookingNotificationAdmin;
use App\Mail\BookingRescheduleAcceptedByUser;
use App\Mail\BookingCancelledNotification;

class BookingController extends Controller
{
public function createBooking()
{
    $unavailabilities = DoctorUnavailability::all(['doctor_id', 'unavailable_date', 'unavailable_start', 'unavailable_end']);
    $branches = Branch::all();

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

    // Step 1: Get all booked slots
    $bookedSlots = Booking::where('booking_status', '!=', 'cancelled')
        ->get()
        ->map(function ($b) use ($services) {
            return [
                'booking_date' => $b->booking_date,
                'booking_time' => $b->booking_time,
                'branch' => $b->branch_id,
                'doctor_id' => $b->doctor_id,
                'service' => $b->service,
                'duration' => $services[$b->service] ?? 30,
            ];
        })->toArray();

    // Step 2: Generate time blocks from doctor unavailability
    $blockedTimes = [];
    foreach ($unavailabilities as $unavailability) {
        $start = Carbon::parse($unavailability->unavailable_start);
        $end = Carbon::parse($unavailability->unavailable_end);

        while ($start < $end) {
            $blockedTimes[] = [
                'booking_date' => $unavailability->unavailable_date,
                'booking_time' => $start->format('H:i:s'),
                'branch' => null, // No specific branch in DoctorUnavailability
                'doctor_id' => $unavailability->doctor_id,
                'service' => null,
                'duration' => 30, // Default block size
            ];
            $start->addMinutes(30);
        }
    }

    // Step 3: Merge both into one list
    $combinedBlocked = array_merge($bookedSlots, $blockedTimes);

    // Step 4: Doctors grouped by branch
    $doctors = Doctor::all();
    $doctorsByBranch = $doctors->groupBy('branch_id')->map(function ($docs) {
        return $docs->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->user->name,
            ];
        });
    });

    // Step 5: Get public holidays
    $holidays = PublicHoliday::pluck('date')->toArray();
    

    // Step 6: Pass everything to view
    return view('user.booking', [
        'branches' => $branches,
        'doctorsByBranch' => $doctorsByBranch,
        'services' => $services,
        'holidays' => $holidays,
        'bookedSlots' => $combinedBlocked,
        'unavailabilities' => $unavailabilities,
    ]);
}

    public function reviewBooking(Request $request)
    {
        $validated = $request->validate([
            'branch' => 'required|exists:branches,id',
            'doctor_id' => 'required|exists:doctors,id',
            'service' => 'required|string',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        session([
            'booking_data' => $validated,
            'deposit_amount' => 10.00
        ]);

        $doctor = Doctor::with('user')->find($validated['doctor_id']);
        $branch = Branch::find($validated['branch']);

        return view('user.payment_review', [
            'data' => $validated,
            'deposit' => 10.00,
            'doctor' => $doctor,
            'branch' => $branch,
        ]);
    }

    public function confirmBooking()
    {
        $data = session('booking_data');
        $deposit = session('deposit_amount', 10.00);

        if (!$data) {
            return redirect()->route('user.booking.create')->with('error', 'Booking session expired. Please try again.');
        }

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'service' => $data['service'],
            'booking_date' => $data['booking_date'],
            'booking_time' => $data['booking_time'],
            'branch_id' => $data['branch'],
            'doctor_id' => $data['doctor_id'],
            'notes' => $data['notes'] ?? null,
            'booking_status' => 'approved',
            'payment_status' => 'deposit_paid',
            'deposit_amount' => $deposit,
        ]);

        $booking->load(['user', 'doctor.user', 'branchInfo']);

        session()->forget('booking_data');
        session()->forget('deposit_amount');
        Mail::to($booking->user->email)->send(new BookingConfirmation($booking));
        // Send to doctor
        Mail::to($booking->doctor->user->email)->send(new BookingNotificationDoctor($booking));

        // Send to all admins of the same branch
        $admins = \App\Models\User::where('role', 'admin')
            ->where('branch_id', $booking->branch_id)
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new BookingNotificationAdmin($booking));
        }


        return redirect()->route('dashboard.user')->with('status', 'Booking confirmed and deposit paid.');
    }

        public function manageBookings(Request $request)
        {
            $query = Booking::where('user_id', auth()->id());

            // ✅ Custom date range
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('booking_date', [$request->from_date, $request->to_date]);
            } elseif ($request->date_filter === 'today') {
                $query->whereDate('booking_date', today());
            } elseif ($request->date_filter === 'week') {
                $query->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($request->date_filter === 'month') {
                $query->whereMonth('booking_date', now()->month)
                    ->whereYear('booking_date', now()->year);
            }

            // ✅ Service filter
            if ($request->filled('service')) {
                $query->where('service', $request->service);
            }

            // ✅ Doctor filter
            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            // ✅ Prioritize reschedule statuses and then earliest date
            $query->orderByRaw("
                CASE 
                    WHEN booking_status = 'reschedule_pending' THEN 1
                    WHEN booking_status = 'rescheduled' THEN 2
                    WHEN booking_status = 'approved' THEN 3
                    WHEN booking_status = 'cancelled' THEN 4
                    ELSE 5
                END ASC,
                booking_date ASC,
                booking_time ASC
            ");

            $bookings = $query->with(['doctor'])->get();
            $doctors = Doctor::whereIn('id', $bookings->pluck('doctor_id')->unique())->get();
            $services = Booking::where('user_id', auth()->id())->pluck('service')->unique();

            return view('user.managebooking', compact('bookings', 'services', 'doctors'));
        }


    public function showRescheduleForm($id)
    {
        $booking = Booking::findOrFail($id);
        $branches = Branch::all();
        return view('user.reschedule', compact('booking', 'branches'));
    }

    public function rescheduleBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'new_booking_date' => 'required|date|after_or_equal:today',
            'new_booking_time' => 'required',
            'doctor_id' => 'required|exists:doctors,id',
            'reason' => 'required|string',
        ]);

        if (PublicHoliday::where('date', $validated['new_booking_date'])->exists()) {
            return back()->withErrors(['new_booking_date' => 'New date is on a public holiday.']);
        }

        $conflict = Booking::where('doctor_id', $validated['doctor_id'])
            ->where('booking_date', $validated['new_booking_date'])
            ->where('booking_time', $validated['new_booking_time'])
            ->where('booking_status', '!=', 'cancelled')
            ->exists();

        if ($conflict) {
            return back()->withErrors(['new_booking_time' => 'Selected time is already booked.']);
        }

        $booking->update(['booking_status' => 'rescheduled']);

        $booking = Booking::create([
            'user_id' => $booking->user_id,
            'service' => $booking->service,
            'booking_date' => $validated['new_booking_date'],
            'booking_time' => $validated['new_booking_time'],
            'branch_id' => $booking->branch_id,
            'doctor_id' => $validated['doctor_id'],
            'notes' => $booking->notes,
            'booking_status' => 'pending',
            'payment_status' => $booking->payment_status ?? 'unpaid',
            'deposit_amount' => $booking->deposit_amount ?? 0,
            'rescheduled_from' => $booking->id,
        ]);

        BookingCancellation::create([
            'booking_id' => $booking->id,
            'reason' => $validated['reason'],
        ]);

        Booking::where('user_id', auth()->id())
            ->where('payment_status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(30))
            ->update(['booking_status' => 'cancelled']);

        return redirect()->route('dashboard.user')->with('status', 'Booking rescheduled.');
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'reason' => 'required|string|max:255',
        ]);

        $booking = Booking::where('id', $request->booking_id)
                          ->where('user_id', auth()->id())
                          ->firstOrFail();

        $booking->booking_status = 'cancelled';
        $booking->notes = 'Cancellation Reason: ' . $request->reason;
        $booking->save();

        return redirect()->route('dashboard.user')->with('status', 'Booking cancelled successfully.');
    }

        public function respondToReschedule(Request $request, $id)
        {
            $booking = Booking::where('user_id', auth()->id())->with(['doctor.user', 'branchInfo'])->findOrFail($id);
            $response = $request->input('response');

            if ($booking->booking_status !== 'rescheduled') {
                return back()->with('error', 'This booking is not marked for reschedule.');
            }

                if ($response === 'accepted') {
                    $booking->booking_status = 'approved';
                    $booking->save();
                    
                    Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmation($booking));

                    // Notify doctor
                    Mail::to($booking->doctor->user->email)->send(new \App\Mail\BookingRescheduleAcceptedByUser($booking));

                    // Notify admin at the branch
                    $adminEmails = \App\Models\User::where('role', 'admin')
                        ->where('branch_id', $booking->branch_id)
                        ->pluck('email')
                        ->toArray();

                    foreach ($adminEmails as $email) {
                        Mail::to($email)->send(new \App\Mail\RescheduleAcceptedByUserNotification($booking));
                    }

                    return redirect()->route('dashboard.user')->with('status', 'Reschedule accepted successfully.');
                }


            if ($response === 'rejected') {
                $booking->booking_status = 'cancelled';
                $booking->payment_status = 'refunded';
                $booking->save();

                // Notify admin of cancellation
                $admin = \App\Models\User::where('role', 'admin')
                    ->where('branch_id', $booking->branch_id)
                    ->first();

                if ($admin) {
                    Mail::to($admin->email)
                        ->send(new \App\Mail\BookingCancelledNotification($booking));
                }

                return redirect()->route('dashboard.user')->with('status', 'Reschedule rejected. Booking has been cancelled. Payment will be refunded within 3-10 business days.');
            }

            return back()->with('error', 'Invalid response.');
        }

    public function cancelBooking(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'reason' => 'required|string|max:255',
        ]);

        $booking = Booking::where('id', $request->booking_id)
                          ->where('user_id', auth()->id())
                          ->firstOrFail();

            $booking->booking_status = 'cancelled';
            $booking->notes = ($booking->notes ? $booking->notes . ' | ' : '') . 'User Cancellation Reason: ' . $request->reason;
            $booking->save();

            // 📧 Email to Doctor
            if ($booking->doctor && $booking->doctor->user) {
                Mail::to($booking->doctor->user->email)->send(new BookingCancelledByUser($booking));
            }

            // 📧 Email to Admin of the branch
            $admin = \App\Models\User::where('role', 'admin')
                ->where('branch_id', $booking->branch_id)
                ->first();

            if ($admin) {
                Mail::to($admin->email)->send(new BookingCancelledByUser($booking));
            }

            return redirect()->back()->with('status', 'Booking cancelled successfully. Note: Deposit is non-refundable.');
    }
}
