<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    // BranchController.php
public function getDoctors($branch_id)
{
    $doctors = Doctor::where('branch_id', $branch_id)->get();
    return response()->json($doctors);
}

}
