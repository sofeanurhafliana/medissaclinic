<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    protected $fillable = [
        'user_id',
        'doctor_id',
        'service',
        'booking_date',
        'booking_time',
        'branch_id',
        'booking_status',
        'notes',
        'rescheduled_from',
        'payment_status', // ✅ MUST BE HERE
        'deposit_amount',
    ];

    // Relationship to user/patient
        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function doctor()
        {
            return $this->belongsTo(Doctor::class, 'doctor_id');
        }
            // Relationship to branch
    public function branchInfo()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Optional: Get original booking if this was a rescheduled one
    public function originalBooking()
    {
        return $this->belongsTo(Booking::class, 'rescheduled_from');
    }
}
