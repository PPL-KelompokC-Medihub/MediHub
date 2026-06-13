<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Support\Concerns\MapsFirestoreData;
use App\Support\Concerns\ResolvesCurrentDoctor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    use MapsFirestoreData;
    use ResolvesCurrentDoctor;

    private const COLLECTION = 'JadwalDokter';

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

        $dokter = $this->currentDoctorObject();

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

        foreach ($this->whereCurrentDoctorOwnerByAnyField($collection, ['dokterid', 'doctor_id']) as $document) {
            $documents[$document['id']] = $document;
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

        foreach ($this->whereCurrentDoctorOwner(self::COLLECTION, 'dokterid') as $schedule) {
            $schedules[$schedule['id']] = $schedule;
        }

        return array_values($schedules);
    }
}
