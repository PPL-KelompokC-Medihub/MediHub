<?php

namespace App\Http\Requests\Pasien;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'string'],
            'doctor_schedule_id' => ['required', 'string'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'patient_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient_gender' => ['nullable', 'string', 'max:30'],
            'patient_weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'patient_height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'allergy_history' => ['nullable', 'string', 'max:1000'],
            'complaint' => ['required', 'string', 'max:2000'],
            'medical_doc' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
        ];
    }
}
