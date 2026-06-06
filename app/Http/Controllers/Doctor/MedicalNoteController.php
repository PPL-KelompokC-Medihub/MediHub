<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalNote;
use App\Models\Prescription;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalNoteController extends Controller
{
    private const APPOINTMENT_COLLECTION = 'BuatJadwalTemu';
    private const DOCTOR_COLLECTION = 'Dokter';

    public function create(Request $request, FirestoreService $firestore)
    {
        $appointmentId = $request->query('appointment_id');
        $patientId = $request->query('patient_id');
        
        $appointment = null;
        if ($appointmentId) {
            $appointmentData = $firestore->find(self::APPOINTMENT_COLLECTION, $appointmentId);
            if ($appointmentData) {
                $appointment = (object) $appointmentData;
                if (empty($patientId)) {
                    $patientId = $appointment->patient_id ?? $appointment->user_uid ?? '';
                }
            }
        }

        return view('dokter.medical_notes.create', compact('appointment', 'appointmentId', 'patientId'));
    }

    public function store(Request $request, FirestoreService $firestore)
    {
        $data = $request->validate([
            'appointment_id' => 'nullable|string',
            'patient_id' => 'nullable|string',
            'notes' => 'required|string',
            'medications' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $doctorId = Auth::id();

        // Create Medical Note
        $note = MedicalNote::create([
            'patient_id' => $data['patient_id'] ?? '',
            'doctor_id' => $doctorId,
            'notes' => $data['notes'],
        ]);

        // Create Prescription if medications are filled
        if (!empty($data['medications'])) {
            Prescription::create([
                'medical_note_id' => $note->id,
                'patient_id' => $data['patient_id'] ?? '',
                'doctor_id' => $doctorId,
                'medications' => $data['medications'],
                'instructions' => $data['instructions'] ?? '',
            ]);
        }

        // If appointment_id is present and complete_appointment checkbox is checked
        if (!empty($data['appointment_id']) && $request->has('complete_appointment')) {
            $appointmentId = $data['appointment_id'];
            $appointment = $firestore->find(self::APPOINTMENT_COLLECTION, $appointmentId);
            
            if ($appointment) {
                // Update appointment status to 'selesai'
                $firestore->update(self::APPOINTMENT_COLLECTION, $appointmentId, [
                    'status' => 'selesai'
                ]);

                // Add notification to patient
                $patientIdVal = (string) ($appointment['patient_id'] ?? $appointment['user_uid'] ?? '');
                if ($patientIdVal !== '') {
                    $firestore->add('Notifications', [
                        'patient_id' => $patientIdVal,
                        'title'      => 'Status Jadwal Temu Diperbarui',
                        'message'    => 'Status jadwal temu Anda telah diubah menjadi: Selesai.',
                        'type'       => 'appointment_status',
                        'read'       => false,
                    ]);
                }
            }
        }

        return redirect()->route('dokter.riwayat')->with('success', 'Catatan medis dan resep obat berhasil disimpan.');
    }
}

