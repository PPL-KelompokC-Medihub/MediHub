<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MedicalNoteController extends Controller
{
    use MapsFirestoreData;

    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const CATATAN_MEDIS_COLLECTION = 'CatatanMedis';
    private const DOCTOR_COLLECTION = 'Dokter';
    private const USERS_COLLECTION = 'Users';

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    /**
     * Form buat catatan medis — halaman terintegrasi per appointment.
     *
     * GET /dokter/catatan-medis/{appointmentId}/create
     */
    public function create(string $appointmentId): View|RedirectResponse
    {
        $userId = (string) Auth::id();

        // Ambil data dokter
        $userData = $this->doctorRepository->findUser($userId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

        // Ambil data appointment
        $appointment = $this->firestore->find(self::APPOINTMENT_COLLECTION, $appointmentId);

        if (! $appointment) {
            return redirect()->route('dokter.dashboard')
                ->with('error', 'Jadwal temu tidak ditemukan.');
        }

        // Cek apakah sudah ada catatan medis untuk appointment ini
        $existingNotes = $this->firestore->where(
            self::CATATAN_MEDIS_COLLECTION,
            'appointment_id',
            '=',
            $appointmentId,
            1,
        );

        if (! empty($existingNotes)) {
            // Redirect ke halaman lihat jika sudah ada
            return redirect()->route('dokter.catatan_medis.show', $appointmentId);
        }

        $appointment = (object) $appointment;

        // Ambil semua appointment dokter ini untuk sidebar
        $allAppointments = $this->fetchDoctorAppointments();

        return view('dokter.catatan-medis', [
            'dokter' => $dokter,
            'appointment' => $appointment,
            'appointments' => $this->toObjects($allAppointments),
            'catatanMedis' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Simpan catatan medis ke Firestore.
     *
     * POST /dokter/catatan-medis
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|string',
            'patient_id' => 'nullable|string',
            'patient_name' => 'nullable|string',
            'keluhan_utama' => 'required|string',
            'hasil_observasi' => 'nullable|string',
            'hasil_asesmen' => 'nullable|string',
            'kesimpulan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'resep_obat' => 'nullable|string',
        ]);

        $userId = (string) Auth::id();
        $doctorId = $this->currentDoctorId();

        // Ambil data dokter untuk nama
        $userData = $this->doctorRepository->findUser($userId);
        $doctorName = $userData['name'] ?? $userData['fullname'] ?? 'Dokter';

        $data = [
            'appointment_id' => $validated['appointment_id'],
            'doctor_id' => $doctorId,
            'doctor_user_id' => $userId,
            'doctor_name' => $doctorName,
            'patient_id' => $validated['patient_id'] ?? '',
            'patient_name' => $validated['patient_name'] ?? '',
            'keluhan_utama' => $validated['keluhan_utama'],
            'hasil_observasi' => $validated['hasil_observasi'] ?? '',
            'hasil_asesmen' => $validated['hasil_asesmen'] ?? '',
            'kesimpulan' => $validated['kesimpulan'] ?? '',
            'rekomendasi' => $validated['rekomendasi'] ?? '',
            'resep_obat' => $validated['resep_obat'] ?? '',
            'created_at' => now()->toIso8601String(),
        ];

        try {
            $this->firestore->add(self::CATATAN_MEDIS_COLLECTION, $data);

            // Update status appointment ke "selesai" jika belum
            $appointment = $this->firestore->find(self::APPOINTMENT_COLLECTION, $validated['appointment_id']);
            if ($appointment) {
                $currentStatus = strtolower(trim((string) ($appointment['status'] ?? '')));
                if ($currentStatus !== 'selesai') {
                    $this->firestore->update(self::APPOINTMENT_COLLECTION, $validated['appointment_id'], [
                        'status' => 'selesai',
                    ]);
                }
            }

            // Kirim notifikasi ke pasien
            $patientId = $validated['patient_id'] ?? '';
            if ($patientId !== '') {
                $this->doctorRepository->createNotification([
                    'patient_id' => $patientId,
                    'title' => 'Catatan Medis Tersedia',
                    'message' => "dr. {$doctorName} telah mengisi catatan medis dan resep obat untuk kunjungan Anda.",
                    'type' => 'medical_record',
                ]);
            }

            return redirect()->route('dokter.catatan_medis.show', $validated['appointment_id'])
                ->with('success', 'Catatan medis & resep obat berhasil disimpan.');
        } catch (\Throwable $e) {
            Log::error('Failed to save catatan medis', [
                'message' => $e->getMessage(),
                'appointment_id' => $validated['appointment_id'],
            ]);

            return back()->withInput()
                ->with('error', 'Gagal menyimpan catatan medis: ' . $e->getMessage());
        }
    }

    /**
     * Lihat catatan medis yang sudah dibuat.
     *
     * GET /dokter/catatan-medis/{appointmentId}
     */
    public function show(string $appointmentId): View|RedirectResponse
    {
        $userId = (string) Auth::id();

        $userData = $this->doctorRepository->findUser($userId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

        // Ambil data appointment
        $appointment = $this->firestore->find(self::APPOINTMENT_COLLECTION, $appointmentId);

        if (! $appointment) {
            return redirect()->route('dokter.dashboard')
                ->with('error', 'Jadwal temu tidak ditemukan.');
        }

        $appointment = (object) $appointment;

        // Cari catatan medis
        $notes = $this->firestore->where(
            self::CATATAN_MEDIS_COLLECTION,
            'appointment_id',
            '=',
            $appointmentId,
            1,
        );

        $catatanMedis = ! empty($notes) ? (object) $notes[0] : null;

        if (! $catatanMedis) {
            return redirect()->route('dokter.catatan_medis.create', $appointmentId);
        }

        // Ambil semua appointment dokter ini untuk sidebar
        $allAppointments = $this->fetchDoctorAppointments();

        return view('dokter.catatan-medis', [
            'dokter' => $dokter,
            'appointment' => $appointment,
            'appointments' => $this->toObjects($allAppointments),
            'catatanMedis' => $catatanMedis,
            'isEdit' => false,
        ]);
    }

    /**
     * Ambil semua appointment milik dokter yang login.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchDoctorAppointments(): array
    {
        $documents = [];

        foreach ($this->currentDoctorOwnerIds() as $doctorId) {
            foreach (['dokterid', 'doctor_id'] as $field) {
                foreach ($this->firestore->where(self::APPOINTMENT_COLLECTION, $field, '=', $doctorId) as $doc) {
                    $documents[$doc['id']] = $doc;
                }
            }
        }

        // Sort by date
        $docs = array_values($documents);
        usort($docs, fn(array $a, array $b): int => strcmp(
            trim(($a['appointment_date'] ?? '') . ' ' . ($a['appointment_time_start'] ?? $a['appointment_time'] ?? '')),
            trim(($b['appointment_date'] ?? '') . ' ' . ($b['appointment_time_start'] ?? $b['appointment_time'] ?? '')),
        ));

        return $docs;
    }

    private function currentDoctorId(): string
    {
        $userId = (string) Auth::id();
        $doctor = $this->firestore->where(self::DOCTOR_COLLECTION, 'usersId', '=', $userId, 1)[0] ?? null;

        return (string) ($doctor['id'] ?? $userId);
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

