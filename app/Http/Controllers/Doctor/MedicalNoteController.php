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
            'patient_id' => 'nullable|integer',
            'notes' => 'required|string',
        ]);

        $data['doctor_id'] = auth()->id() ?? $request->input('doctor_id');

        MedicalNote::create($data);

        return redirect()->back()->with('success', 'Catatan medis tersimpan.');
    }
}
