<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $table = 'public_holidays';
    public $timestamps = false; // assuming you don’t have created_at/updated_at
    protected $fillable = ['date']; // include any other fields if needed
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class PublicHoliday extends Model
// {
    // use HasFactory;

    // protected $table = 'public_holidays';

    // public $timestamps = true;

    // protected $fillable = ['date', 'name']; // 🚨 Must include 'date'
// }

