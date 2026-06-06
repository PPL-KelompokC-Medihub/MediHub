<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function create(Request $request)
    {
        return redirect()->route('dokter.medical_notes.create', [
            'patient_id' => $request->query('patient_id'),
            'appointment_id' => $request->query('appointment_id'),
        ]);
    }

    public function store(Request $request)
    {
        return redirect()->route('dokter.medical_notes.create');
    }
}

