<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Support\Concerns\MapsFirestoreData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Services\MedihubFirestoreRepository;

class ScheduleController extends Controller
{
    use MapsFirestoreData;

    private const COLLECTION = 'JadwalDokter';
    private const DOCTOR_COLLECTION = 'Dokter';

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function index(): View
    {
        $jadwal = $this->toObjects($this->currentDoctorSchedules());

        // Kelompokkan per minggu
        $mingguIni = [];
        $mingguDepan = [];

        $startMingguIni = now()->startOfWeek();
        $endMingguIni = now()->endOfWeek();
        $startMingguDepan = now()->addWeek()->startOfWeek();
        $endMingguDepan = now()->addWeek()->endOfWeek();

        foreach ($jadwal as $j) {
            $tanggal = \Carbon\Carbon::parse($j->tanggal ?? null);
            if ($tanggal->between($startMingguIni, $endMingguIni)) {
                $mingguIni[] = $j;
            } elseif ($tanggal->between($startMingguDepan, $endMingguDepan)) {
                $mingguDepan[] = $j;
            }
        }

        // Ambil data appointments untuk sidebar
        $allAppointments = $this->sortAppointments(
            $this->currentDoctorDocuments('BuatJadwalTemu'),
        );

        $appointments = $this->toObjects(array_map(
            fn (array $appointment): array => array_merge($appointment, [
                'display_status' => $this->appointmentStatusLabel($appointment),
                'status_key' => $this->appointmentStatusKey($appointment),
                'display_date' => $this->appointmentDateLabel($appointment),
                'display_time' => $this->appointmentTimeLabel($appointment),
                'display_complaint' => $this->appointmentComplaint($appointment),
            ]),
            $allAppointments,
        ));

        $userId = (string) Auth::id();
        $userData = $this->doctorRepository->findUser($userId);
        $dokter = $userData
            ? (object) $this->doctorRepository->hydrateDoctorData($userData)
            : null;

        return view('dokter.jadwal', compact('mingguIni', 'mingguDepan', 'jadwal', 'appointments', 'dokter'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|string',
            'jam_selesai' => 'required|string',
        ]);

        $this->firestore->add(self::COLLECTION, [
            'dokterid' => $this->currentDoctorId(),
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|string',
            'jam_selesai' => 'required|string',
        ]);

        $this->abortIfScheduleIsNotOwnedByCurrentDoctor($id);

        $this->firestore->update(self::COLLECTION, $id, $validated);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $this->abortIfScheduleIsNotOwnedByCurrentDoctor($id);

        $this->firestore->delete(self::COLLECTION, $id);

        return response()->json(['success' => true]);
    }

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

    private function sortAppointments(array $appointments): array
    {
        usort($appointments, fn (array $left, array $right): int => strcmp(
            trim((string) ($left['appointment_date'] ?? '').' '.(string) ($left['appointment_time_start'] ?? $left['appointment_time'] ?? '')),
            trim((string) ($right['appointment_date'] ?? '').' '.(string) ($right['appointment_time_start'] ?? $right['appointment_time'] ?? '')),
        ));

        return $appointments;
    }

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

    private function appointmentStatusLabel(array $appointment): string
    {
        return match ($this->appointmentStatusKey($appointment)) {
            'dibatalkan' => 'Dibatalkan',
            'selesai' => 'Selesai',
            'diperiksa' => 'Diperiksa',
            default => 'Menunggu',
        };
    }

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

    private function appointmentDateLabel(array $appointment): string
    {
        $date = trim((string) ($appointment['appointment_date'] ?? ''));

        return $date !== '' ? substr($date, 0, 10) : '-';
    }

    private function appointmentComplaint(array $appointment): string
    {
        $complaint = trim((string) ($appointment['complaint'] ?? ''));

        return $complaint !== '' ? $complaint : '-';
    }

    private function currentDoctorId(): string
    {
        $userId = (string) Auth::id();
        $doctor = $this->firestore->where(self::DOCTOR_COLLECTION, 'usersId', '=', $userId, 1)[0] ?? null;

        return (string) ($doctor['id'] ?? $userId);
    }

    private function abortIfScheduleIsNotOwnedByCurrentDoctor(string $id): void
    {
        $schedule = $this->firestore->find(self::COLLECTION, $id);

        abort_if(! $schedule, 404);
        abort_if(! in_array((string) ($schedule['dokterid'] ?? ''), $this->currentDoctorOwnerIds(), true), 403);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function currentDoctorSchedules(): array
    {
        $schedules = [];

        foreach ($this->currentDoctorOwnerIds() as $doctorId) {
            foreach ($this->firestore->where(self::COLLECTION, 'dokterid', '=', $doctorId) as $schedule) {
                $schedules[$schedule['id']] = $schedule;
            }
        }

        return array_values($schedules);
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
