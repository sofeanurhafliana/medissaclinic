<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorUnavailability extends Model
{
    protected $fillable = [
        'doctor_id',
        'unavailable_date',
        'unavailable_start',
        'unavailable_end',
        'note',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
