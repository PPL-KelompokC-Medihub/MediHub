<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
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

        $pasien = $this->firestore->find('Pasien', $uid) ?? [
            'id' => $uid,
            'user_id' => $uid,
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'created_at' => now()->toIso8601String(),
        ];

        $userData = $this->firestore->find('Users', $uid) ?? [];

        $mergedUser = array_merge($pasien, [
            'fullname' => $userData['fullname'] ?? $authUser->fullname ?? $authUser->name ?? '',
            'email' => $userData['email'] ?? $authUser->email ?? '',
            'role' => $userData['role'] ?? 'pasien',
        ]);

        return view('pasien.profile', [
            'user' => (object) $mergedUser,
        ]);
    }

    public function update(Request $request)
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return redirect()->route('login-pasien');
        }

        $uid = $authUser->id;

        $validated = $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'umur' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'gender' => ['nullable', 'string', 'in:Perempuan,Pria'],
            'blood_type' => ['nullable', 'string', 'in:A,B,AB,O'],
            'allergy_history' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'code_pos' => ['nullable', 'string', 'max:100'],
            'no_allergy' => ['nullable'],
        ]);

        $existing = $this->firestore->find('Pasien', $uid) ?? [];

        unset(
            $existing['fullname'],
            $existing['email'],
            $existing['role'],
            $existing['update_at']
        );

        $payload = array_merge($existing, [
            'id' => $uid,
            'user_id' => $uid,
            'umur' => $validated['umur'] ?? $existing['umur'] ?? null,
            'weight' => $validated['weight'] ?? $existing['weight'] ?? null,
            'height' => $validated['height'] ?? $existing['height'] ?? null,
            'gender' => $validated['gender'] ?? $existing['gender'] ?? '',
            'blood_type' => $validated['blood_type'] ?? $existing['blood_type'] ?? '',
            'allergy_history' => $validated['allergy_history'] ?? $existing['allergy_history'] ?? '',
            'country' => $validated['country'] ?? $existing['country'] ?? 'Indonesia',
            'city' => $validated['city'] ?? $existing['city'] ?? 'Bandung',
            'code_pos' => $validated['code_pos'] ?? $existing['code_pos'] ?? '',
            'no_allergy' => $request->has('no_allergy'),
            'created_at' => $existing['created_at'] ?? now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->firestore->set('Pasien', $uid, $payload);

        $this->firestore->update('Users', $uid, [
            'fullname' => $validated['fullname'] ?? '',
            'email' => $validated['email'] ?? '',
            'role' => 'pasien',
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->firestore->deleteFields('Pasien', $uid, [
            'fullname',
            'email',
            'role',
            'update_at',
        ]);

        return redirect()
            ->route('pasien.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request)
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return redirect()->route('login-pasien');
        }

        $request->validate([
            'profile_pict' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $uid = $authUser->id;

        $path = $request->file('profile_pict')->store('profile-pictures', 'public');

        $this->firestore->update('Pasien', $uid, [
            'profile_pict' => $path,
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()
            ->route('pasien.profile')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroyAccount(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login-pasien');
        }

        $uid = $user->id;

        $this->firestore->delete('Pasien', $uid);
        $this->firestore->delete('Users', $uid);
        $this->firestore->auth()->deleteUser($uid);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('register-pasien')
            ->with('success', 'Akun berhasil dihapus.');
    }
}