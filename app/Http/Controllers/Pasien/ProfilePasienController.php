<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilePasienController extends Controller
{
    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function index()
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return redirect()->route('login-pasien');
        }

        $uid = $authUser->id;

        $pasien = $this->firestore->find('Pasien', $uid);

        if (! $pasien) {
            $pasien = [
                'id' => $uid,
                'user_id' => $uid,
                'fullname' => $authUser->fullname ?? $authUser->name ?? '',
                'email' => $authUser->email ?? '',
                'role' => 'pasien',
                'age' => null,
                'weight' => null,
                'height' => null,
                'allergy_history' => '',
                'city' => 'Bandung',
                'country' => 'Indonesia',
                'created_at' => $authUser->created_at ?? null,
            ];
        }

        $user = (object) $pasien;

        return view('pasien.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return redirect()->route('login-pasien');
        }

        $uid = $authUser->id;

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],

            'umur' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],

            'allergy_history' => ['nullable', 'string', 'max:1000'],

            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'code_pos' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'in:Perempuan,Pria'],
            'blood_type' => ['nullable', 'string', 'in:A,B,AB,O'],
            'no_allergy' => ['nullable', 'boolean'],
        ]);

        $existingPasien = $this->firestore->find('Pasien', $uid) ?? [];

        $payload = array_merge($existingPasien, [
            'id' => $uid,
            'user_id' => $uid,

            'fullname' => $validated['fullname'] ?? ($existingPasien['fullname'] ?? ''),
            'email' => $validated['email'] ?? ($existingPasien['email'] ?? ''),
            'role' => 'pasien',

            'umur' => $request->filled('umur')
                ? $validated['umur']
                : ($existingPasien['umur'] ?? null),

            'weight' => $request->filled('weight')
                ? $validated['weight']
                : ($existingPasien['weight'] ?? null),

            'height' => $request->filled('height')
                ? $validated['height']
                : ($existingPasien['height'] ?? null),

            'allergy_history' => $request->filled('allergy_history')
                ? $validated['allergy_history']
                : ($existingPasien['allergy_history'] ?? ''),

            'country' => $request->filled('country')
                ? $validated['country']
                : ($existingPasien['country'] ?? 'Indonesia'),

            'city' => $request->filled('city')
                ? $validated['city']
                : ($existingPasien['city'] ?? 'Bandung'),

            'code_pos' => $request->filled('code_pos')
                ? $validated['code_pos']
                : ($existingPasien['code_pos'] ?? ''),

            'gender' => $request->filled('gender')
                ? $validated['gender']
                : ($existingPasien['gender'] ?? ''),

            'blood_type' => $request->filled('blood_type')
                ? $validated['blood_type']
                : ($existingPasien['blood_type'] ?? ''),

            'no_allergy' => $request->boolean('no_allergy'),

            'created_at' => $existingPasien['created_at'] ?? now()->toIso8601String(),
            'update_at' => now()->toIso8601String(),
        ]);

        $this->firestore->set('Pasien', $uid, $payload);

        $this->firestore->update('Users', $uid, [
            'fullname' => $payload['fullname'],
            'email' => $payload['email'],
            'update_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('pasien.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroyAccount(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login-pasien');
        }

        $uid = $user->id ?? null;

        if ($uid) {
            $this->firestore->delete('Pasien', $uid);
            $this->firestore->delete('Users', $uid);
            $this->firestore->auth()->deleteUser($uid);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register-pasien')
            ->with('success', 'Akun berhasil dihapus.');
    }
}