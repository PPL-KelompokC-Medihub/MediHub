<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use App\Support\MapsFirestoreData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use MapsFirestoreData;

    private const DOCTOR_COLLECTION = 'Dokter';
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';

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
            $this->firestore->where('JadwalDokter', 'dokterid', '=', $doctorId)
        );

        $totalPasien = count($appointments);
        $jadwalHariIni = collect($appointments)->filter(fn($a) =>
            isset($a->appointment_date) &&
            str_starts_with($a->appointment_date, now()->toDateString())
        )->count();

        return view('dashboard.index', compact(
            'dokter',
            'appointments',
            'jadwalSaya',
            'totalPasien',
            'jadwalHariIni',
        ));
    }
}