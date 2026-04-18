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

Route::get('/doctor/profile-form-demo', function () {
    return view('doctor.profile-form');
})->name('doctor.profile-form.demo');

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showSignIn'])->name('sign-in');
    Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('sign-up');

    Route::get('/login-dokter', function () {
        return view('auth.login-dokter');
    })->name('login-dokter');

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/layanan', [DoctorController::class, 'services'])->name('services');
    Route::get('/doctor/{id}', [DoctorController::class, 'show'])->name('doctor.show');
    Route::get('/facility/{id}', [FacilityController::class, 'show'])->name('facility.show');
    Route::get('/booking/{doctorId}', [AppointmentController::class, 'create'])->name('booking.create');
    Route::post('/booking', [AppointmentController::class, 'store'])->name('booking.store');
    Route::get('/appointments', [AppointmentController::class, 'history'])->name('appointments.history');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
