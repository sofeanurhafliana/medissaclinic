<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCancellation extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['booking_id', 'reason', 'cancelled_at'];

    public function booking() {
        return $this->belongsTo(Booking::class);
    }
}

