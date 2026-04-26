<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirestoreService;

class DashboardPasienController extends Controller
{
    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index()
    {
        $categories = [
            ['nama' => 'Umum', 'icon' => 'fa-heart-pulse'],
            ['nama' => 'Anak', 'icon' => 'fa-heart'],
            ['nama' => 'Penyakit Dalam', 'icon' => 'fa-heart-circle-bolt'],
            ['nama' => 'Bedah', 'icon' => 'fa-syringe'],
            ['nama' => 'Gigi & Mulut', 'icon' => 'fa-tooth'],
            ['nama' => 'Kandungan', 'icon' => 'fa-person-pregnant'],
            ['nama' => 'Jantung', 'icon' => 'fa-heart-circle-plus'],
        ];

        $doctors = [
            [
                'nama' => 'dr. Arief Nugroho, Sp.JP',
                'spesialis' => 'Spesialis Jantung dan Pembuluh Darah',
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?q=80&w=500&auto=format&fit=crop',
            ],
            [
                'nama' => 'dr. Ratna Dewi Sp.A',
                'spesialis' => 'Spesialis Anak',
                'rating' => '4.9',
                'pasien' => '450+ Total Pasien',
                'foto' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=500&auto=format&fit=crop',
            ],
            [
                'nama' => 'dr. Andini Pratama, Sp.PD',
                'spesialis' => 'Spesialis Penyakit Dalam',
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?q=80&w=500&auto=format&fit=crop',
            ],
        ];

        $appointments = [
            [
                'jenis' => 'Dokter Umum',
                'rs' => 'RS Medic Center - Bandung',
                'antrian' => '02',
                'tanggal' => '02 September 2025',
                'jam' => '13:00 WIB - 13:15 WIB',
                'hari' => 'Hari ini',
            ],
            [
                'jenis' => 'Psikolog',
                'rs' => 'RS Medic Center - Bandung',
                'antrian' => '05',
                'tanggal' => '05 September 2025',
                'jam' => '13:00 WIB - 14:30 WIB',
                'hari' => 'Minggu, 05 September 2025',
            ],
        ];

        return view('pasien.beranda', compact('categories', 'doctors', 'appointments'));
    }

    public function profile()
    {
        $user = auth()->user();

        return view('pasien.profile', compact('user'));
    }

    public function destroyAccount(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login-pasien');
        }

        $uid = $user->id ?? null;

        if ($uid) {
            // Hapus dari Firestore Users
            $this->firestore->delete('Users', $uid);

            // Hapus dari Firebase Authentication
            $this->firestore->auth()->deleteUser($uid);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register-pasien')
            ->with('success', 'Akun berhasil dihapus.');
    }
}