<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| File ini cuma sebagai entry point. Route sebenarnya dipisah per
| role/peran agar mudah dicari saat menambah fitur baru:
|
|   routes/public.php   -> halaman tanpa login (landing, dll)
|   routes/auth.php     -> login, register, logout
|   routes/dokter.php   -> endpoint role dokter
|   routes/pasien.php   -> endpoint role pasien
|
| Lihat docs/ARCHITECTURE.md untuk struktur folder selengkapnya.
|
*/

require __DIR__ . '/public.php';

Route::middleware('guest')->group(base_path('routes/auth.php'));

Route::get('/dashboard', function (Request $request) {
    $user = Auth::user();
    $userData = $user && method_exists($user, 'getAttributes') ? $user->getAttributes() : [];

    $role = strtolower(trim((string) ($userData['role'] ?? $request->session()->get('medihub_user_role', ''))));
    $role = match ($role) {
        'patient' => 'pasien',
        'doctor' => 'dokter',
        default => $role,
    };

    if ($role === 'pasien') {
        return redirect()->route('pasien.beranda');
    }

    if ($role === 'dokter') {
        return redirect()->route('dokter.dashboard');
    }

    Auth::guard('web')->forgetUser();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login-pasien');
})->middleware('auth')->name('dashboard');

require __DIR__ . '/dokter.php';
require __DIR__ . '/pasien.php';
