<?php

namespace App\Services\Pasien;

use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
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
        protected MedihubFirestoreRepository $medihubFirestoreRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function formData(string $selectedDoctorId = ''): array
    {
        $doctors = $this->buildDoctorOptions();
        $schedules = $this->buildScheduleOptions();
        $availableDoctorIds = array_map(
            fn (array $doctor): string => (string) ($doctor['id'] ?? ''),
            $doctors,
        );

        if ($selectedDoctorId === '' || ! in_array($selectedDoctorId, $availableDoctorIds, true)) {
            $selectedDoctorId = $this->firstDoctorIdWithSchedule($schedules)
                ?? $availableDoctorIds[0]
                ?? '';
        }

        return [
            'patient' => $this->resolvePatientData(),
            'doctors' => $doctors,
            'schedules' => $schedules,
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
        if (! $schedule || ! $this->scheduleBelongsToDoctor($schedule, $doctor, $data['doctor_id'])) {
            throw ValidationException::withMessages([
                'doctor_schedule_id' => 'Jadwal dokter tidak tersedia.',
            ]);
        }

        if (! $this->appointmentTimeIsInsideSchedule($data['appointment_time'], $schedule)) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Jam temu tidak sesuai dengan jadwal dokter.',
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

        $appointmentAlreadyBooked = collect($existingAppointments)->contains(
            fn (array $appointment): bool => ($appointment['appointment_time'] ?? null) === $data['appointment_time']
        );

        if ($appointmentAlreadyBooked) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Jam temu ini sudah dibooking pasien lain.',
            ]);
        }

        $this->firestore->add(self::APPOINTMENT_COLLECTION, [
            'user_uid' => Auth::id(),
            'patient_id' => Auth::id(),
            'dokterid' => $data['doctor_id'],
            'doctor_id' => $data['doctor_id'],
            'doctor_schedule_id' => $data['doctor_schedule_id'],
            'appointment_time_start' => $data['appointment_time'],
            'appointment_time_end' => $this->appointmentTimeEnd($data['appointment_time']),
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
        $doctorIdsByUserId = $this->doctorIdsByUserId();
        $doctorUserIdsByDoctorId = array_flip($doctorIdsByUserId);
        $doctorNamesById = $this->doctorNamesById();
        $bookedTimesBySchedule = $this->bookedTimesBySchedule();
        $schedules = $this->firestore->all(self::DOCTOR_SCHEDULE_COLLECTION);

        usort($schedules, fn (array $a, array $b): int => strcmp(
            (string) ($a['tanggal'] ?? ''),
            (string) ($b['tanggal'] ?? ''),
        ));

        return array_map(fn (array $schedule): array => [
            'id' => $schedule['id'],
            'doctor_id' => $doctorIdsByUserId[$schedule['dokterid'] ?? ''] ?? $schedule['dokterid'] ?? '',
            'doctor_user_id' => $doctorUserIdsByDoctorId[$schedule['dokterid'] ?? ''] ?? $schedule['dokterid'] ?? '',
            'doctor_name' => $doctorNamesById[$doctorIdsByUserId[$schedule['dokterid'] ?? ''] ?? $schedule['dokterid'] ?? ''] ?? '',
            'date' => $schedule['tanggal'] ?? '',
            'start' => $schedule['jam_mulai'] ?? '',
            'end' => $schedule['jam_selesai'] ?? '',
            'booked_times' => $bookedTimesBySchedule[$schedule['id'] ?? ''] ?? [],
        ], $schedules);
    }

    /**
     * @param array<int, array<string, mixed>> $schedules
     */
    private function firstDoctorIdWithSchedule(array $schedules): ?string
    {
        foreach ($schedules as $schedule) {
            $doctorId = (string) ($schedule['doctor_id'] ?? '');

            if ($doctorId !== '') {
                return $doctorId;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $schedule
     * @param array<string, mixed> $doctor
     */
    private function scheduleBelongsToDoctor(array $schedule, array $doctor, string $doctorId): bool
    {
        $scheduleDoctorId = (string) ($schedule['dokterid'] ?? '');

        return $scheduleDoctorId === $doctorId
            || $scheduleDoctorId === (string) ($doctor['usersId'] ?? '');
    }

    /**
     * @param array<string, mixed> $schedule
     */
    private function appointmentTimeIsInsideSchedule(string $appointmentTime, array $schedule): bool
    {
        $appointment = $this->parseTimeToMinutes($appointmentTime);
        $start = $this->parseTimeToMinutes($schedule['jam_mulai'] ?? null);
        $end = $this->parseTimeToMinutes($schedule['jam_selesai'] ?? null);

        if ($appointment === null || $start === null || $end === null || $start >= $end) {
            return false;
        }

        return $appointment >= $start
            && $appointment < $end
            && (($appointment - $start) % 30) === 0;
    }

    private function appointmentTimeEnd(string $appointmentTime): ?string
    {
        $appointment = $this->parseTimeToMinutes($appointmentTime);

        return $appointment === null ? null : $this->formatMinutes($appointment + 30);
    }

    private function parseTimeToMinutes(mixed $time): ?int
    {
        if (! is_string($time) || ! preg_match('/^\d{2}:\d{2}$/', $time)) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $time));

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return ($hour * 60) + $minute;
    }

    private function formatMinutes(int $totalMinutes): string
    {
        $normalized = (($totalMinutes % 1440) + 1440) % 1440;
        $hour = str_pad((string) intdiv($normalized, 60), 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string) ($normalized % 60), 2, '0', STR_PAD_LEFT);

        return "{$hour}:{$minute}";
    }

    /**
     * @return array<string, string>
     */
    private function doctorIdsByUserId(): array
    {
        $doctorIds = [];

        foreach ($this->firestore->all(self::DOCTOR_COLLECTION) as $doctor) {
            $userId = (string) ($doctor['usersId'] ?? '');
            $doctorId = (string) ($doctor['id'] ?? '');

            if ($userId !== '' && $doctorId !== '') {
                $doctorIds[$userId] = $doctorId;
            }
        }

        return $doctorIds;
    }

    /**
     * @return array<string, string>
     */
    private function doctorNamesById(): array
    {
        $userNames = [];
        foreach ($this->firestore->all(self::USERS_COLLECTION) as $user) {
            $userNames[(string) ($user['id'] ?? '')] = (string) ($user['fullname'] ?? $user['email'] ?? '');
        }

        $names = [];
        foreach ($this->firestore->all(self::DOCTOR_COLLECTION) as $doctor) {
            $doctorId = (string) ($doctor['id'] ?? '');
            $userId = (string) ($doctor['usersId'] ?? '');

            if ($doctorId !== '') {
                $names[$doctorId] = $userNames[$userId] ?? (string) ($doctor['email'] ?? '');
            }
        }

        return $names;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function bookedTimesBySchedule(): array
    {
        $bookedTimes = [];

        foreach ($this->firestore->all(self::APPOINTMENT_COLLECTION) as $appointment) {
            $scheduleId = (string) ($appointment['doctor_schedule_id'] ?? '');
            $appointmentTime = (string) ($appointment['appointment_time'] ?? '');

            if ($scheduleId !== '' && $appointmentTime !== '') {
                $bookedTimes[$scheduleId][] = $appointmentTime;
            }
        }

        return array_map(
            fn (array $times): array => array_values(array_unique($times)),
            $bookedTimes,
        );
    }
    /**
     * Hapus / batalkan appointment pasien.
     */
    public function deleteAppointment(array $appointmentIds): void
    {
        foreach ($appointmentIds as $appointmentId) {
            $appointmentId = (string) $appointmentId;

            if ($appointmentId === '') {
                continue;
            }

            $this->medihubFirestoreRepository->deleteAppointment($appointmentId);
        }
    }
}
