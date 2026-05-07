<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\FirebaseSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Login & registrasi untuk dokter dan pasien. Semuanya melewati
| FirebaseSessionController karena auth source-of-truth-nya di Firebase.
| File ini di-include dari routes/web.php dengan middleware "guest".
|
*/

Route::get('/login', fn () => redirect()->route('login-dokter'))->name('login');

Route::get('/sign-in', [AuthController::class, 'showSignIn'])->name('sign-in');
Route::get('/sign-up', [AuthController::class, 'showSignUp'])->name('sign-up');

// --- Dokter ---
Route::get('/login-dokter', function () {
    return view('auth.dokter.login');
})->name('login-dokter');

Route::post('/login-dokter', [FirebaseSessionController::class, 'login'])
    ->middleware('throttle:10,1');

Route::get('/register-dokter', function () {
    return view('auth.dokter.register');
})->name('register-dokter');

// --- Pasien ---
Route::get('/login-pasien', function () {
    return view('auth.pasien.login');
})->name('login-pasien');

Route::get('/register-pasien', function () {
    return view('auth.pasien.register');
})->name('register-pasien');

// --- Firebase session bridge (shared) ---
Route::post('/auth/firebase/session', [FirebaseSessionController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('firebase.session.login');

Route::post('/auth/firebase/register', [FirebaseSessionController::class, 'register'])
    ->middleware('throttle:10,1')
    ->name('firebase.register');
