<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Dashboard utama dokter setelah login.
 *
 * Menampilkan ringkasan: total pasien, jadwal hari ini, jadwal yang
 * sudah dibuat, dan daftar booking pasien yang masuk.
 */
class DashboardController extends Controller
{
    use MapsFirestoreData;

    private const DOCTOR_COLLECTION = 'Dokter';
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const SCHEDULE_COLLECTION = 'JadwalDokter';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): View
    {
        $doctorId = Auth::id();

        $dokter = $this->firestore->find(self::DOCTOR_COLLECTION, $doctorId);
        $dokter = $dokter ? $this->toObject($dokter) : null;

        $appointments = $this->toObjects(
            $this->firestore->where(self::APPOINTMENT_COLLECTION, 'dokterid', '=', $doctorId)
        );

        $jadwalSaya = $this->toObjects(
            $this->firestore->where(self::SCHEDULE_COLLECTION, 'dokterid', '=', $doctorId)
        );

        $totalPasien = count($appointments);
        $jadwalHariIni = collect($appointments)->filter(fn ($a) =>
            isset($a->appointment_date) &&
            str_starts_with($a->appointment_date, now()->toDateString())
        )->count();

        return view('dokter.dashboard', compact(
            'dokter',
            'appointments',
            'jadwalSaya',
            'totalPasien',
            'jadwalHariIni',
        ));
    }
}
