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
            ['nama' => 'Umum', 'icon' => 'Umum.png'],
            ['nama' => 'Anak', 'icon' => 'Anak.png'],
            ['nama' => 'Penyakit Dalam', 'icon' => 'Penyakit-dalam.png'],
            ['nama' => 'Bedah', 'icon' => 'Bedah.png'],
            ['nama' => 'Gigi & Mulut', 'icon' => 'Gigi & Mulut.png'],
            ['nama' => 'Kandungan', 'icon' => 'Kandungan.png'],
            ['nama' => 'Jantung', 'icon' => 'Jantung.png'],
        ];

        $userDocuments = $this->firestore->all('Users');
        $doctorDocuments = $this->firestore->all('Dokter');
        $specializationDocuments = $this->firestore->all('Dokter_spesialisasi');

        $users = [];

        foreach ($userDocuments as $user) {
            if (($user['role'] ?? null) === 'dokter') {
                $users[$user['id']] = $user;
            }
        }

        $specializations = [];

        foreach ($specializationDocuments as $specialization) {
            if (isset($specialization['dokterid'])) {
                $specializations[$specialization['dokterid']] = $specialization;
            }
        }

        $doctors = [];

        foreach ($doctorDocuments as $doctor) {
            $doctorId = $doctor['id'] ?? null;
            $userId = $doctor['usersId'] ?? null;

            if (! $doctorId || ! $userId) {
                continue;
            }

            $user = $users[$userId] ?? null;

            if (! $user) {
                continue;
            }

            $specialist = $specializations[$doctorId] ?? null;

            $doctors[] = [
                'nama' => 'dr. ' . ($user['fullname'] ?? $user['name'] ?? 'Tidak Diketahui'),
                'spesialis' => $specialist['service'] ?? 'Tidak Diketahui',
                'spesialis_key' => strtolower($specialist['service'] ?? 'Tidak Diketahui'),
                'rating' => '5.0',
                'pasien' => '450+ Total Pasien',
                'foto' => null,
            ];
        }

        $appointments = [];

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
            $this->firestore->delete('Users', $uid);
            $this->firestore->auth()->deleteUser($uid);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register-pasien')
            ->with('success', 'Akun berhasil dihapus.');
    }
}