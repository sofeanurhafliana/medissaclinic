<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'branch_id', 'name', 'email','profile_picture'];

    // ✅ Fix: Add this relationship
        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings()
{
    return $this->hasMany(Booking::class, 'doctor_id');
}

public function unavailabilities()
{
    return $this->hasMany(DoctorUnavailability::class);
}


}

