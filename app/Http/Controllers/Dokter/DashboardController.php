<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use MapsFirestoreData;

    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const SCHEDULE_COLLECTION = 'JadwalDokter';

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function index(): View
    {
        $doctorId = (string) Auth::id();

        // Ambil data dokter lengkap termasuk profile_pict via repository
        $userData = $this->doctorRepository->findUser($doctorId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

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