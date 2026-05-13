<?php

use App\Http\Controllers\Pasien\BookingController;
use App\Http\Controllers\Pasien\DashboardController;
use App\Http\Controllers\Pasien\ProfileController;
use App\Http\Controllers\Public\DokterPublicController;
use App\Http\Controllers\Public\FacilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patient Routes
|--------------------------------------------------------------------------
|
| Endpoint role pasien. Konvensi nama route: prefix `pasien.*` agar
| sinkron dengan view di resources/views/pasien/*.
|
| Controller pasien hidup di App\Http\Controllers\Pasien\* — class name
| pendek (DashboardController, ProfileController, BookingController) karena
| folder yang disambiguate dari Dokter\.
|
*/

Route::middleware(['auth', 'role:pasien'])->group(function () {
    // Beranda & layanan (PBI-08, PBI-09)
    Route::get('/pasien/beranda', [DashboardController::class, 'index'])
        ->name('pasien.beranda');
    Route::get('/pasien/layanan', [DashboardController::class, 'layanan'])
        ->name('pasien.layanan');

    // Profil pasien (PBI-12 / PBI-05)
    Route::get('/pasien/profile', [ProfileController::class, 'index'])
        ->name('pasien.profile');
    Route::put('/pasien/profile', [ProfileController::class, 'update'])
        ->name('pasien.profile.update');
    Route::delete('/pasien/account', [ProfileController::class, 'destroyAccount'])
        ->name('pasien.destroy-account');
    Route::put('/pasien/profile/photo', [ProfileController::class, 'updatePhoto'])
        ->name('pasien.profile.update-photo');

    // Booking jadwal temu (PBI-11 / KFP-06)
    Route::get('/pasien/booking', [BookingController::class, 'create'])
        ->name('pasien.booking.create');
    Route::post('/pasien/booking', [BookingController::class, 'store'])
        ->name('pasien.booking.store');
    Route::delete('/pasien/booking/delete', [BookingController::class, 'destroy'])
        ->name('pasien.booking.destroy');
});

// --- Halaman publik untuk pasien (katalog dokter & fasilitas RS) ---
// Tetap memerlukan auth + profil dokter completed (kalau dokter yang akses)
// agar dokter belum lengkap profilnya tidak bisa nyasar ke sini.
Route::middleware(['auth', 'dokter.profile.completed'])->group(function () {
    // PBI-09 daftar layanan & PBI-10 daftar dokter
    Route::get('/layanan', [DokterPublicController::class, 'services'])->name('services');
    Route::get('/dokter/{id}', [DokterPublicController::class, 'show'])->name('dokter.show');
    Route::get('/facility/{id}', [FacilityController::class, 'show'])->name('facility.show');
});
