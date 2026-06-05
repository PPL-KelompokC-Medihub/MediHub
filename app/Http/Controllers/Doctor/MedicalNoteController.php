<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalNote;
use Illuminate\Http\Request;

class MedicalNoteController extends Controller
{
    public function create()
    {
        return view('dokter.medical_notes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|string',
            'notes' => 'required|string',
        ]);

        $data['doctor_id'] = auth()->id() ?? $request->input('doctor_id');

        $note = MedicalNote::create($data);

        if ($request->filled('patient_id')) {
            return redirect()->route('dokter.prescriptions.create', [
                'patient_id' => $note->patient_id,
                'medical_note_id' => $note->id,
            ])->with('success', 'Catatan medis tersimpan. Silakan tulis resep jika diperlukan.');
        }

        return redirect()->route('dokter.dashboard')->with('success', 'Catatan medis tersimpan.');
    }
}
