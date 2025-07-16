<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model {
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'active'];


    public function doctors() {
        return $this->hasMany(Doctor::class);
    }

    public function users()
{
    return $this->hasMany(User::class);
}

public function admin()
{
    return $this->hasOne(User::class)->where('role', 'admin');
}

}

