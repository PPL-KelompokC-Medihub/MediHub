<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use App\Support\MapsFirestoreData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use MapsFirestoreData;

    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const DOCTOR_COLLECTION = 'Dokter';
    private const DEFAULT_BOOKING_DATE = '26 Agustus 2026';
    private const DEFAULT_BOOKING_TIME = '09:00';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): JsonResponse
    {
        $appointments = $this->attachDoctorRecords($this->firestore->all(self::APPOINTMENT_COLLECTION));

        return response()->json($appointments);
    }

    public function create(string $doctorId): View
    {
        $doctor = $this->firestore->find(self::DOCTOR_COLLECTION, $doctorId);

        if (! $doctor) {
            abort(404);
        }

        $doctor = $this->toObject($doctor);
        $date = self::DEFAULT_BOOKING_DATE;
        $time = self::DEFAULT_BOOKING_TIME;

        return view('appointment.booking', compact('doctor', 'date', 'time'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|string',
            'patient_name' => 'required|string|max:255',
            'appointment_date' => 'required|date',
        ]);

        $doctor = $this->firestore->find(self::DOCTOR_COLLECTION, $validated['doctor_id']);
        if (! $doctor) {
            return back()->withErrors(['doctor_id' => 'Dokter tidak ditemukan.']);
        }

        $data = [
            'dokterid' => $validated['doctor_id'],
            'patient_name' => $validated['patient_name'],
            'appointment_date' => $validated['appointment_date'],
            'user_uid' => $this->resolveCurrentUserUid(),
        ];

        $appointment = $this->firestore->add(self::APPOINTMENT_COLLECTION, $data);

        if ($request->wantsJson()) {
            return response()->json($appointment, 201);
        }

        return redirect()->route('appointments.history')->with('success', 'Appointment booked successfully!');
    }

    public function history(): View
    {
        $appointments = $this->firestore->where(
            self::APPOINTMENT_COLLECTION,
            'user_uid',
            '=',
            $this->resolveCurrentUserUid(),
        );
        $appointments = $this->attachDoctorRecords($appointments, true);
        $appointments = $this->toObjects($appointments);

        return view('appointment.history', compact('appointments'));
    }

    private function resolveCurrentUserUid(): string|int|null
    {
        return Auth::id();
    }

    /**
     * @param array<int, array<string, mixed>> $appointments
     * @return array<int, array<string, mixed>>
     */
    private function attachDoctorRecords(array $appointments, bool $castDoctorToObject = false): array
    {
        return array_map(function (array $appointment) use ($castDoctorToObject): array {
            $doctorId = $appointment['dokterid'] ?? $appointment['doctor_id'] ?? null;
            if (! $doctorId) {
                return $appointment;
            }

            $doctorData = $this->firestore->find(self::DOCTOR_COLLECTION, (string) $doctorId);
            if (! $doctorData) {
                $appointment['doctor'] = null;

                return $appointment;
            }

            $appointment['doctor'] = $castDoctorToObject ? $this->toObject($doctorData) : $doctorData;

            return $appointment;
        }, $appointments);
    }
}
