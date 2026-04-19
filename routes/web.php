<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FirebaseSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');



Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => redirect()->route('login-dokter'))->name('login');

    Route::get('/sign-in', [AuthController::class, 'showSignIn'])->name('sign-in');
    Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('sign-up');

    Route::get('/login-dokter', function () {
        return view('auth.login-dokter');
    })->name('login-dokter');
    Route::post('/login-dokter', [FirebaseSessionController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::get('/register-dokter', function () {
        return view('auth.register-dokter');
    })->name('register-dokter');

    Route::post('/auth/firebase/session', [FirebaseSessionController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('firebase.session.login');
    Route::post('/auth/firebase/register', [FirebaseSessionController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('firebase.register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/doctor/profile-form', [ProfileController::class, 'showDoctorForm'])->name('doctor.profile-form');
    Route::post('/doctor/profile-form', [ProfileController::class, 'updateDoctorForm'])->name('doctor.profile.update');
    Route::get('/doctor/profile-expertise', [ProfileController::class, 'showDoctorExpertiseForm'])->name('doctor.profile.expertise');
    Route::post('/doctor/profile-expertise', [ProfileController::class, 'updateDoctorExpertiseForm'])->name('doctor.profile.expertise.update');
    Route::get('/doctor/profile-certification', [ProfileController::class, 'showDoctorCertificationForm'])->name('doctor.profile.certification');
    Route::post('/doctor/profile-certification', [ProfileController::class, 'updateDoctorCertificationForm'])->name('doctor.profile.certification.update');
});

Route::middleware(['auth', 'doctor.profile.completed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/layanan', [DoctorController::class, 'services'])->name('services');
    Route::get('/doctor/{id}', [DoctorController::class, 'show'])->name('doctor.show');
    Route::get('/facility/{id}', [FacilityController::class, 'show'])->name('facility.show');
    Route::get('/booking/{doctorId}', [AppointmentController::class, 'create'])->name('booking.create');
    Route::post('/booking', [AppointmentController::class, 'store'])->name('booking.store');
    Route::get('/appointments', [AppointmentController::class, 'history'])->name('appointments.history');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
});
