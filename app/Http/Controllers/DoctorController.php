<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use App\Support\MapsFirestoreData;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DoctorController extends Controller
{
    use MapsFirestoreData;

    private const COLLECTION = 'Dokter';
    private const TIME_SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];
    private const AVAILABLE_SLOTS = [
        ['day' => 'Senin', 'date' => '26 Ags', 'available' => true],
        ['day' => 'Selasa', 'date' => '27 Ags', 'available' => true],
        ['day' => 'Rabu', 'date' => '28 Ags', 'available' => false],
        ['day' => 'Kamis', 'date' => '29 Ags', 'available' => true],
        ['day' => 'Jumat', 'date' => '30 Ags', 'available' => true],
    ];

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index(): JsonResponse
    {
        $doctors = $this->firestore->all(self::COLLECTION);

        return response()->json($doctors);
    }

    public function show(string $id): View
    {
        $doctor = $this->firestore->find(self::COLLECTION, $id);

        if (! $doctor) {
            abort(404);
        }

        $availableSlots = self::AVAILABLE_SLOTS;
        $timeSlots = self::TIME_SLOTS;
        $doctor = $this->toObject($doctor);

        return view('doctor.show', compact('doctor', 'availableSlots', 'timeSlots'));
    }

    public function services(): View
    {
        $doctors = $this->firestore->all(self::COLLECTION);
        $specialties = array_values(array_filter(array_unique(array_column($doctors, 'specialty'))));
        $doctors = $this->toObjects($doctors);

        return view('doctor.services', compact('doctors', 'specialties'));
    }
}
