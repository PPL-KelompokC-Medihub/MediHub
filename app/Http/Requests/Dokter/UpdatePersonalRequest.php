<?php

namespace App\Http\Requests\Dokter;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi data pribadi dokter (Step 1 onboarding).
 *
 * Sumber: PBI-04 / KFD-03.
 */
class UpdatePersonalRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'weight' => ['required', 'numeric', 'min:20', 'max:300'],
            'height' => ['required', 'numeric', 'min:100', 'max:250'],
            'gender' => ['required', 'string', 'in:Perempuan,Pria'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'profile_pict' => ['nullable', 'string', 'max:255'], // Bisa berupa URL atau path penyimpanan
        ];
    }
}
