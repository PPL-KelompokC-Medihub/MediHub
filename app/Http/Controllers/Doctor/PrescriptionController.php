<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function create()
    {
        return view('dokter.prescriptions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medical_note_id' => 'nullable|integer',
            'patient_id' => 'nullable|string',
            'medications' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        $data['doctor_id'] = auth()->id() ?? $request->input('doctor_id');

        Prescription::create($data);

        return redirect()->route('dokter.dashboard')->with('success', 'Resep obat berhasil disimpan.');
    }
}
