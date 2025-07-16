<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\BranchController;
use App\Models\Doctor;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;


Route::get('/', [AuthController::class, 'index'])->name('home');


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showForm'])->name('register');
Route::post('/register', [AuthController::class, 'processForm'])->name('register.process');


/*
|--------------------------------------------------------------------------
| Authenticated Routes (Grouped by Role)
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Bookings
    Route::get('/bookings', [AdminController::class, 'manageBookings'])->name('bookings');
    Route::get('/bookings/sort', [AdminController::class, 'sortBookings'])->name('bookings.sort');
    Route::put('/bookings/{id}', [AdminController::class, 'updateBookingStatus'])->name('bookings.update');
    Route::get('/bookings/{id}/reschedule', [AdminController::class, 'showRescheduleForm'])->name('bookings.reschedule');

    //User
    Route::delete('patients/{id}/deactivate', [AdminController::class, 'deactivatePatient'])->name('patients.deactivate');
    Route::post('patients/{id}/restore', [AdminController::class, 'restorePatient'])->name('patients.restore');
    Route::get('/patients', [AdminController::class, 'patientsIndex'])->name('patients.index');
    Route::get('/patients/{id}/history', [AdminController::class, 'viewPatientHistory'])->name('patients.history');

    //Branch
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings/update', [AdminController::class, 'updateBranch'])->name('settings.update');

    //admin
    Route::put('profile/update', [AdminController::class, 'updateProfile'])->name('update.profile');


    // Doctors
    Route::get('/doctors', [AdminController::class, 'doctorsIndex'])->name('doctors.index');
    Route::get('/doctors/create', [AdminController::class, 'create'])->name('doctors.create');
    Route::post('/doctors/store', [AdminController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/{id}/edit', [AdminController::class, 'edit'])->name('doctors.edit');
    Route::put('/doctors/{id}', [AdminController::class, 'update'])->name('doctors.update');
    Route::delete('/doctors/{id}', [AdminController::class, 'destroy'])->name('doctors.destroy');
});


// User Routes
    Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [AuthController::class, 'userDashboard'])->name('dashboard.user');
    Route::get('/account-settings', function () {
        return view('user.settings');
    })->name('user.settings');

    Route::put('/user/update-profile', [AuthController::class, 'updateProfile'])->name('user.update.profile');
    Route::delete('/account/deactivate', [AuthController::class, 'softDeleteAccount'])->name('user.softdelete');

    // Booking
    Route::get('/user/booking/create', [BookingController::class, 'createBooking'])->name('user.booking.create');
    Route::get('/user/managebooking', [BookingController::class, 'manageBookings'])->name('user.manage.booking');
    Route::post('/user/booking/{id}/reschedule-response', [BookingController::class, 'respondToReschedule'])->name('user.booking.response');
    Route::post('/user/booking/review', [BookingController::class, 'reviewBooking'])->name('user.booking.review');
    Route::post('/user/booking/confirm', [BookingController::class, 'confirmBooking'])->name('user.booking.confirm');




    

    // Cancel
    Route::post('/user/booking/cancel', [BookingController::class, 'cancelBooking'])->name('user.booking.cancel');
});


// Doctor Routes
Route::middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/doctor/dashboard', [AuthController::class, 'doctorDashboard'])->name('dashboard.doctor');
    Route::get('/doctor/schedule', [DoctorController::class, 'viewSchedule'])->name('doctor.schedule.view');
    Route::middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/doctor/availability', [DoctorController::class, 'viewAvailability'])->name('doctor.availability.view');
    Route::post('/doctor/availability', [DoctorController::class, 'updateSchedule'])->name('doctor.schedule.update');
    Route::get('/doctor/schedule/confirm', [DoctorController::class, 'confirmUnavailability'])->name('doctor.schedule.confirm');
    Route::post('/doctor/schedule/confirm', [DoctorController::class, 'finalizeUnavailability'])->name('doctor.schedule.finalize');
    Route::get('/doctor/manage_bookings', [DoctorController::class, 'manageBookings'])->name('doctor.manage.bookings');
    Route::post('/doctor/bookings/{booking}/note', [DoctorController::class, 'updateBookingNote'])->name('doctor.update.booking.note');
    
    Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/settings', [DoctorController::class, 'settings'])->name('settings');
    Route::put('/settings', [DoctorController::class, 'updateProfile'])->name('settings.update');
});

});

});


/*
|--------------------------------------------------------------------------
| Shared Routes (auth only)
|--------------------------------------------------------------------------
*/



Route::middleware('auth')->group(function () {
    // AJAX for branch -> doctor list
    Route::get('/branches/{id}/doctors', [BranchController::class, 'getDoctors'])->name('branch.doctors');
});
