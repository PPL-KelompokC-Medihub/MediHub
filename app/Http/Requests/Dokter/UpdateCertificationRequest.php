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
       
        $user = $this->user();
        $userId = (string) $user->getAuthIdentifier();
        
        $repository = app(\App\Services\MedihubFirestoreRepository::class);
        $userData = $repository->hydrateDoctorData($repository->findUser($userId));

        return [
            'str_document'     => [blank($userData['STR'] ?? null)           ? 'required' : 'nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
            'sip_document'     => [blank($userData['SIP'] ?? null)           ? 'required' : 'nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
            'ijazah_doctor'    => [blank($userData['ijazah_doctor'] ?? null) ? 'required' : 'nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
            'ktp_document'     => [blank($userData['KTP'] ?? null)           ? 'required' : 'nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
            'profile_pict'     => [blank($userData['profile_pict'] ?? null)  ? 'required' : 'nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'certifications'   => [blank($userData['certification1'] ?? null) ? 'required' : 'nullable', 'array', 'min:1', 'max:6'],
            'certifications.*' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:2048'],
        ];
    }
}
