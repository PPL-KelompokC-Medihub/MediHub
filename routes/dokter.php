<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dokter\AppointmentStatusController;
use App\Http\Controllers\Dokter\DashboardController;
use App\Http\Controllers\Dokter\ProfileController;
use App\Http\Controllers\Dokter\ScheduleController;
use App\Http\Controllers\Doctor\MedicalNoteController;
use App\Http\Controllers\Doctor\PrescriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dokter Routes
|--------------------------------------------------------------------------
|
| Semua endpoint role dokter setelah login. Aturan konsistensi:
|
|   URL path  : selalu /dokter/...
|   Route name: selalu dokter.<resource>.<action>
|
| Pengecekan kelengkapan profil dokter diatur lewat middleware
| `dokter.profile.completed` yang auto-redirect ke 3-step onboarding
| kalau profil belum lengkap.
|
| Logout TIDAK pakai prefix dokter karena dipakai bersama (dokter & pasien).
|
*/

// 3-step onboarding profil dokter (perlu auth tapi belum perlu profil completed)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:dokter'])->group(function () {
    Route::get('/dokter/profile/personal', [ProfileController::class, 'showPersonal'])
        ->name('dokter.profile.personal');
    Route::post('/dokter/profile/personal', [ProfileController::class, 'updatePersonal'])
        ->name('dokter.profile.personal.update');

    Route::get('/dokter/profile/expertise', [ProfileController::class, 'showExpertise'])
        ->name('dokter.profile.expertise');
    Route::post('/dokter/profile/expertise', [ProfileController::class, 'updateExpertise'])
        ->name('dokter.profile.expertise.update');

    Route::get('/dokter/profile/certification', [ProfileController::class, 'showCertification'])
        ->name('dokter.profile.certification');
    Route::post('/dokter/profile/certification', [ProfileController::class, 'updateCertification'])
        ->name('dokter.profile.certification.update');
});

// Endpoint utama (memerlukan profil dokter sudah lengkap)
Route::middleware(['auth', 'role:dokter', 'dokter.profile.completed'])->group(function () {
    // Halaman utama dokter
    Route::get('/dokter/dashboard', [DashboardController::class, 'index'])
        ->name('dokter.dashboard');

    // Halaman riwayat jadwal temu dokter
    Route::get('/dokter/riwayat', [DashboardController::class, 'riwayat'])
        ->name('dokter.riwayat');

    // PBI-13: Halaman Profil Dokter (read-only view)
    Route::get('/dokter/profil', [ProfileController::class, 'show'])
        ->name('dokter.profil.show');

    // Profil — alias ke step 1 onboarding agar dokter bisa edit data dirinya
    Route::get('/dokter/profile', [ProfileController::class, 'showPersonal'])
        ->name('dokter.profile');

    // CRUD jadwal praktik dokter (KFD-04 / PBI-07)
    Route::get('/dokter/jadwal', [ScheduleController::class, 'index'])
        ->name('dokter.jadwal.index');
    Route::post('/dokter/jadwal', [ScheduleController::class, 'store'])
        ->name('dokter.jadwal.store');
    Route::put('/dokter/jadwal/{id}', [ScheduleController::class, 'update'])
        ->name('dokter.jadwal.update');
    Route::delete('/dokter/jadwal/{id}', [ScheduleController::class, 'destroy'])
        ->name('dokter.jadwal.destroy');

// Update status pasien (KFD-07 / PBI-17)
    Route::patch('/dokter/appointment/{id}/status', [AppointmentStatusController::class, 'update'])
        ->name('dokter.appointment.update-status');

    // Medical notes & prescriptions
    Route::get('/dokter/medical-notes/create', [MedicalNoteController::class, 'create'])
        ->name('dokter.medical_notes.create');
    Route::post('/dokter/medical-notes', [MedicalNoteController::class, 'store'])
        ->name('dokter.medical_notes.store');

    Route::get('/dokter/prescriptions/create', [PrescriptionController::class, 'create'])
        ->name('dokter.prescriptions.create');
    Route::post('/dokter/prescriptions', [PrescriptionController::class, 'store'])
        ->name('dokter.prescriptions.store');
});
