<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $userId = (string) Auth::id();

        $userData = $this->doctorRepository->findUser($userId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));

        $allAppointments = $this->sortAppointments(
            $this->currentDoctorDocuments(self::APPOINTMENT_COLLECTION),
        );
        $filteredAppointments = $this->filterAppointments($allAppointments, $search, $date);

        $appointments = $this->toObjects(array_map(
            fn (array $appointment): array => array_merge($appointment, [
                'display_status' => $this->appointmentStatusLabel($appointment),
                'status_key' => $this->appointmentStatusKey($appointment),
                'display_date' => $this->appointmentDateLabel($appointment),
                'display_time' => $this->appointmentTimeLabel($appointment),
                'display_complaint' => $this->appointmentComplaint($appointment),
            ]),
            $filteredAppointments,
        ));
        $jadwalSaya = $this->toObjects($this->currentDoctorDocuments(self::SCHEDULE_COLLECTION));

        $activeAppointments = array_values(array_filter(
            $allAppointments,
            fn (array $appointment): bool => ! in_array($this->appointmentStatusKey($appointment), ['dibatalkan', 'selesai'], true),
        ));

        $totalPasien = count(array_unique(array_map(
            fn (array $appointment): string => $this->patientIdentityKey($appointment),
            $activeAppointments,
        )));
        $jadwalHariIni = count(array_filter(
            $allAppointments,
            fn (array $appointment): bool => $this->appointmentDateStartsWith($appointment, now()->toDateString()),
        ));
        $sesiSelesaiBulanIni = count(array_filter(
            $allAppointments,
            fn (array $appointment): bool => $this->appointmentStatusKey($appointment) === 'selesai'
                && $this->appointmentDateStartsWith($appointment, now()->format('Y-m')),
        ));

        return view('dokter.dashboard', compact(
            'dokter',
            'appointments',
            'jadwalSaya',
            'totalPasien',
            'jadwalHariIni',
            'sesiSelesaiBulanIni',
            'search',
            'date',
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $appointments
     * @return array<int, array<string, mixed>>
     */
    private function filterAppointments(array $appointments, string $search, string $date): array
    {
        if ($search === '' && $date === '') {
            return $appointments;
        }

        return array_values(array_filter(
            $appointments,
            fn (array $appointment): bool => $this->appointmentMatchesSearch($appointment, $search)
                && ($date === '' || $this->appointmentDateStartsWith($appointment, $date)),
        ));
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentMatchesSearch(array $appointment, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $needle = strtolower($search);

        foreach (['patient_name', 'patient_email', 'complaint', 'status'] as $field) {
            if (str_contains(strtolower((string) ($appointment[$field] ?? '')), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentDateStartsWith(array $appointment, string $date): bool
    {
        return str_starts_with((string) ($appointment['appointment_date'] ?? ''), $date);
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentStatusKey(array $appointment): string
    {
        $status = strtolower(trim((string) ($appointment['status'] ?? 'menunggu')));

        return match ($status) {
            'batal', 'dibatalkan', 'cancelled', 'canceled' => 'dibatalkan',
            'selesai', 'done', 'completed' => 'selesai',
            'diperiksa', 'sedang diperiksa', 'in progress' => 'diperiksa',
            default => 'menunggu',
        };
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentStatusLabel(array $appointment): string
    {
        return match ($this->appointmentStatusKey($appointment)) {
            'dibatalkan' => 'Dibatalkan',
            'selesai' => 'Selesai',
            'diperiksa' => 'Diperiksa',
            default => 'Menunggu',
        };
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentTimeLabel(array $appointment): string
    {
        $start = trim((string) ($appointment['appointment_time_start'] ?? $appointment['appointment_time'] ?? ''));
        $end = trim((string) ($appointment['appointment_time_end'] ?? ''));
        $range = trim((string) ($appointment['schedule_time_range'] ?? ''));

        if ($start === '') {
            return $range !== '' ? $range : '-';
        }

        return trim($start.($end !== '' ? ' - '.$end : ''));
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentDateLabel(array $appointment): string
    {
        $date = trim((string) ($appointment['appointment_date'] ?? ''));

        return $date !== '' ? substr($date, 0, 10) : '-';
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function appointmentComplaint(array $appointment): string
    {
        $complaint = trim((string) ($appointment['complaint'] ?? ''));

        return $complaint !== '' ? $complaint : '-';
    }

    /**
     * @param  array<string, mixed>  $appointment
     */
    private function patientIdentityKey(array $appointment): string
    {
        foreach (['patient_id', 'user_uid', 'patient_email', 'patient_name'] as $field) {
            $value = trim((string) ($appointment[$field] ?? ''));

            if ($value !== '') {
                return strtolower($value);
            }
        }

        return (string) ($appointment['id'] ?? spl_object_id((object) $appointment));
    }

    /**
     * @param  array<int, array<string, mixed>>  $appointments
     * @return array<int, array<string, mixed>>
     */
    private function sortAppointments(array $appointments): array
    {
        usort($appointments, fn (array $left, array $right): int => strcmp(
            trim((string) ($left['appointment_date'] ?? '').' '.(string) ($left['appointment_time_start'] ?? $left['appointment_time'] ?? '')),
            trim((string) ($right['appointment_date'] ?? '').' '.(string) ($right['appointment_time_start'] ?? $right['appointment_time'] ?? '')),
        ));

        return $appointments;
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
            foreach (['dokterid', 'doctor_id'] as $field) {
                foreach ($this->firestore->where($collection, $field, '=', $doctorId) as $document) {
                    $documents[$document['id']] = $document;
                }
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
