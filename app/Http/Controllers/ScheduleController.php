<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function checkAvailability(Request $request)
{
    $exists = Booking::where('doctor_id', $request->doctor_id)
        ->where('booking_date', $request->booking_date)
        ->where('time_slot', $request->time_slot)
        ->exists();

    $holidays = PublicHoliday::where('holiday_date', $request->booking_date)->exists();

    if ($exists || $holidays) {
        return response()->json(['available' => false]);
    }
    return response()->json(['available' => true]);
}

}
