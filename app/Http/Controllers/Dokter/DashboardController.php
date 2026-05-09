<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
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
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function index(): View
    {
        $userId = (string) Auth::id();

        // Ambil data dokter lengkap termasuk profile_pict via repository
        $userData = $this->doctorRepository->findUser($userId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

        $appointments = $this->toObjects($this->currentDoctorDocuments(self::APPOINTMENT_COLLECTION));

        $jadwalSaya = $this->toObjects($this->currentDoctorDocuments(self::SCHEDULE_COLLECTION));

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

    private function currentDoctorId(): string
    {
        $userId = (string) Auth::id();
        $doctor = $this->firestore->where(self::DOCTOR_COLLECTION, 'usersId', '=', $userId, 1)[0] ?? null;

        return (string) ($doctor['id'] ?? $userId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function currentDoctorDocuments(string $collection): array
    {
        $documents = [];

        foreach ($this->currentDoctorOwnerIds() as $doctorId) {
            foreach ($this->firestore->where($collection, 'dokterid', '=', $doctorId) as $document) {
                $documents[$document['id']] = $document;
            }
        }

        return array_values($documents);
    }

    /**
     * @return array<int, string>
     */
    private function currentDoctorOwnerIds(): array
    {
        return array_values(array_unique([
            $this->currentDoctorId(),
            (string) Auth::id(),
        ]));
    }
}
