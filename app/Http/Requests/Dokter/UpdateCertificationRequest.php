<?php

namespace App\Http\Requests\Dokter;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi dokumen & sertifikasi dokter (Step 3 onboarding).
 *
 * Wajib upload STR, SIP, ijazah, KTP, foto profil, plus minimal 1
 * sertifikasi (maksimal 6 sesuai dokumen requirement).
 *
 * Sumber: PBI-04 / KFD-03.
 */
class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $role = strtolower(trim((string) ($user->role ?? '')));
        if ($role === '') {
            $role = strtolower(trim((string) request()->session()->get('medihub_user_role', '')));
        }

        return in_array($role, ['dokter', 'doctor'], true);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'str_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'sip_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'ijazah_doctor' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'ktp_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'profile_pict' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'certifications' => ['required', 'array', 'min:1', 'max:6'],
            'certifications.*' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
        ];
    }
}
