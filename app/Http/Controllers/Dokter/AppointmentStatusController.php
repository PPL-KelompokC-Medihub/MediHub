<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Update status jadwal temu pasien oleh dokter.
 *
 * Dokter dapat mengubah status antrian pasien:
 *   menunggu → diperiksa → selesai
 *
 * Sumber: PBI-17 / KFD-07.
 */
class AppointmentStatusController extends Controller
{
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const DOCTOR_COLLECTION = 'Dokter';

    private const ALLOWED_STATUSES = ['menunggu', 'diperiksa', 'selesai'];

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * Update status pasien.
     *
     * PATCH /dokter/appointment/{id}/status
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_STATUSES)],
        ]);

        $appointment = $this->firestore->find(self::APPOINTMENT_COLLECTION, $id);

        if (! $appointment) {
            return response()->json(['success' => false, 'message' => 'Jadwal temu tidak ditemukan.'], 404);
        }

        // Pastikan appointment ini milik dokter yang sedang login
        $doctorId = $this->currentDoctorId();
        $appointmentDoctorId = (string) ($appointment['doctor_id'] ?? $appointment['dokterid'] ?? '');

        if (! in_array($appointmentDoctorId, $this->currentDoctorOwnerIds(), true)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $newStatus = $validated['status'];

        $updateData = ['status' => $newStatus];

        // Tambahkan notifikasi ke koleksi Notifications untuk pasien
        $patientId = (string) ($appointment['patient_id'] ?? $appointment['user_uid'] ?? '');
        if ($patientId !== '') {
            $statusLabel = match ($newStatus) {
                'diperiksa' => 'Diperiksa',
                'selesai'   => 'Selesai',
                default     => 'Menunggu',
            };
            $this->firestore->add('Notifications', [
                'patient_id' => $patientId,
                'title'      => 'Status Jadwal Temu Diperbarui',
                'message'    => "Status jadwal temu Anda telah diubah menjadi: {$statusLabel}.",
                'type'       => 'appointment_status',
                'read'       => false,
            ]);
        }

        $this->firestore->update(self::APPOINTMENT_COLLECTION, $id, $updateData);

        $statusLabel = match ($newStatus) {
            'diperiksa' => 'Diperiksa',
            'selesai'   => 'Selesai',
            default     => 'Menunggu',
        };

        return response()->json([
            'success'      => true,
            'message'      => "Status berhasil diubah menjadi {$statusLabel}.",
            'new_status'   => $newStatus,
            'status_label' => $statusLabel,
        ]);
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
