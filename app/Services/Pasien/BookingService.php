<?php

namespace App\Services\Pasien;

use App\Services\FirestoreService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const DOCTOR_COLLECTION = 'Dokter';
    private const DOCTOR_SCHEDULE_COLLECTION = 'JadwalDokter';
    private const PATIENT_COLLECTION = 'Pasien';
    private const USERS_COLLECTION = 'Users';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function formData(string $selectedDoctorId = ''): array
    {
        return [
            'patient' => $this->resolvePatientData(),
            'doctors' => $this->buildDoctorOptions(),
            'schedules' => $this->buildScheduleOptions(),
            'selectedDoctorId' => $selectedDoctorId,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAppointment(array $data, ?UploadedFile $medicalDoc): void
    {
        $doctor = $this->firestore->find(self::DOCTOR_COLLECTION, $data['doctor_id']);
        if (! $doctor) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Dokter tidak ditemukan.',
            ]);
        }

        $schedule = $this->firestore->find(self::DOCTOR_SCHEDULE_COLLECTION, $data['doctor_schedule_id']);
        if (! $schedule || ($schedule['dokterid'] ?? null) !== $data['doctor_id']) {
            throw ValidationException::withMessages([
                'doctor_schedule_id' => 'Jadwal dokter tidak tersedia.',
            ]);
        }

        $medicalDocPath = $medicalDoc?->store(
            'patient/' . Auth::id() . '/medical-documents',
            'public',
        );

        $existingAppointments = $this->firestore->where(
            self::APPOINTMENT_COLLECTION,
            'doctor_schedule_id',
            '=',
            $data['doctor_schedule_id'],
        );

        $this->firestore->add(self::APPOINTMENT_COLLECTION, [
            'user_uid' => Auth::id(),
            'patient_id' => Auth::id(),
            'dokterid' => $data['doctor_id'],
            'doctor_id' => $data['doctor_id'],
            'doctor_schedule_id' => $data['doctor_schedule_id'],
            'patient_name' => $data['patient_name'],
            'patient_email' => $data['patient_email'] ?? null,
            'patient_age' => $data['patient_age'] ?? null,
            'patient_gender' => $data['patient_gender'] ?? null,
            'patient_weight' => $data['patient_weight'] ?? null,
            'patient_height' => $data['patient_height'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'allergy_history' => $data['allergy_history'] ?? null,
            'complaint' => $data['complaint'],
            'medical_doc' => $medicalDocPath,
            'appointment_date' => $schedule['tanggal'] ?? null,
            'appointment_time' => $data['appointment_time'],
            'schedule_time_range' => trim(($schedule['jam_mulai'] ?? '') . ' - ' . ($schedule['jam_selesai'] ?? '')),
            'queue_number' => count($existingAppointments) + 1,
            'status' => 'Menunggu',
            'cancellation_reason' => null,
            'update_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePatientData(): array
    {
        $uid = (string) Auth::id();
        $authUser = Auth::user();
        $patient = $this->firestore->find(self::PATIENT_COLLECTION, $uid) ?? [];

        return [
            'fullname' => $patient['fullname'] ?? $authUser?->fullname ?? $authUser?->name ?? '',
            'email' => $patient['email'] ?? $authUser?->email ?? '',
            'umur' => $patient['umur'] ?? $patient['age'] ?? null,
            'gender' => $patient['gender'] ?? '',
            'weight' => $patient['weight'] ?? null,
            'height' => $patient['height'] ?? null,
            'blood_type' => $patient['blood_type'] ?? '',
            'allergy_history' => $patient['allergy_history'] ?? '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDoctorOptions(): array
    {
        $users = [];
        foreach ($this->firestore->all(self::USERS_COLLECTION) as $user) {
            $users[$user['id']] = $user;
        }

        $specializations = [];
        foreach ($this->firestore->all('Dokter_spesialisasi') as $specialization) {
            if (isset($specialization['dokterid'])) {
                $specializations[$specialization['dokterid']] = $specialization;
            }
        }

        return array_values(array_map(function (array $doctor) use ($users, $specializations): array {
            $user = $users[$doctor['usersId'] ?? ''] ?? [];
            $specialization = $specializations[$doctor['id'] ?? ''] ?? [];

            return [
                'id' => $doctor['id'],
                'name' => $user['fullname'] ?? $doctor['email'] ?? 'Dokter',
                'specialization' => $specialization['service'] ?? $specialization['main_specialization'] ?? 'Spesialis Umum',
            ];
        }, $this->firestore->all(self::DOCTOR_COLLECTION)));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildScheduleOptions(): array
    {
        $schedules = $this->firestore->all(self::DOCTOR_SCHEDULE_COLLECTION);

        usort($schedules, fn (array $a, array $b): int => strcmp(
            (string) ($a['tanggal'] ?? ''),
            (string) ($b['tanggal'] ?? ''),
        ));

        return array_map(fn (array $schedule): array => [
            'id' => $schedule['id'],
            'doctor_id' => $schedule['dokterid'] ?? '',
            'date' => $schedule['tanggal'] ?? '',
            'start' => $schedule['jam_mulai'] ?? '',
            'end' => $schedule['jam_selesai'] ?? '',
        ], $schedules);
    }
}
