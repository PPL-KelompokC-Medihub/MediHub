<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_note_id',
        'patient_id',
        'doctor_id',
        'medications',
        'instructions',
    ];

    public function medicalNote()
    {
        return $this->belongsTo(MedicalNote::class);
    }
}
