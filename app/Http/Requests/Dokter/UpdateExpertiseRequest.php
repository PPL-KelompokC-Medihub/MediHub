<?php

namespace App\Http\Requests\Dokter;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi keahlian dokter (Step 2 onboarding).
 *
 * Sumber: PBI-04 / KFD-03.
 */
class UpdateExpertiseRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'specialty' => ['required', 'string', 'max:100'],
            'sub_specialty' => ['nullable', 'string', 'max:100'],
            'started_practice_year' => ['required', 'integer', 'min:1950', 'max:' . now()->year],
            'education_institution' => ['required', 'string', 'max:255'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'string', 'max:100'],
            'bio' => ['required', 'string', 'max:2000'],
        ];
    }
}
