<?php

namespace App\Http\Controllers;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Support\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use RuntimeException;

class FirebaseSessionController extends Controller
{
    private const USERS_COLLECTION = 'Users';
    private const PASIEN_COLLECTION = 'Pasien';
    private const VALID_ROLES = ['dokter', 'pasien'];

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id_token' => ['nullable', 'string', 'required_without_all:email,password'],
            'email' => ['nullable', 'email', 'required_without:id_token'],
            'password' => ['nullable', 'string', 'required_without:id_token'],
            'role' => ['nullable', 'string', 'in:dokter,pasien'],
        ], [
            'id_token.required_without_all' => 'Email dan kata sandi harus diisi.',
            'email.required_without' => 'Email harus diisi.',
            'password.required_without' => 'Kata sandi harus diisi.',
        ]);

        try {
            $idToken = $this->resolveIdTokenForLogin($validated);
            $verifiedIdToken = $this->firestore->auth()->verifyIdToken($idToken);
        } catch (RuntimeException $e) {
            Log::warning('Firebase credentials/config error', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, $e->getMessage(), 422);
        } catch (FailedToVerifyToken $e) {
            Log::warning('Firebase token verification failed', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, 'Token Firebase tidak valid.', 422, $e->getMessage());
        } catch (\Throwable $e) {
            Log::warning('Firebase sign-in failed', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, $e->getMessage(), 422);
        }

        $uid = (string) $verifiedIdToken->claims()->get('sub');
        $email = (string) $verifiedIdToken->claims()->get('email');
        $name = '';
        $emailVerified = (bool) $verifiedIdToken->claims()->get('email_verified');

        if (! $uid || ! $email) {
            return $this->loginErrorResponse($request, 'Data akun Firebase tidak lengkap.', 422);
        }

        if (! $emailVerified) {
            return $this->loginErrorResponse(
                $request,
                'Akun belum diverifikasi. Silakan verifikasi email terlebih dahulu sebelum login.',
                403,
            );
        }

        try {
            $userData = $this->upsertUser(
                uid: $uid,
                email: $email,
                name: $name,
                emailVerified: $emailVerified,
                role: $validated['role'] ?? null,
            );
            if (($userData['role'] ?? null) === 'dokter') {
                $this->doctorRepository->ensureDoctorRecord($userData);
                $userData = $this->doctorRepository->hydrateDoctorData($userData);
            }
        } catch (RuntimeException $e) {
            Log::warning('Firestore user sync failed', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected Firestore user sync failure', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, 'Login gagal saat menyimpan sesi pengguna.', 500);
        }

        $user = new FirestoreUser($userData);

        // Firestore-backed auth does not store a local password hash,
        // so Laravel's "remember me" cookie flow cannot be used safely here.
        Auth::login($user, false);
        $request->session()->regenerate();

        // Dokter yang belum mengisi profil dikirim ke form profil
        $redirectUrl = $this->resolvePostLoginRedirect($userData);

        if (! $request->expectsJson() && ! $request->wantsJson()) {
            return redirect($redirectUrl);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'redirect' => $redirectUrl,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:dokter,pasien'],
        ], [
            'id_token.required' => 'Gagal memverifikasi dengan Firebase. Pastikan koneksi dan API Key valid.',
            'role.required' => 'Peran (role) harus dipilih.',
        ]);

        try {
            $verifiedIdToken = $this->firestore->auth()->verifyIdToken($validated['id_token']);
        } catch (RuntimeException $e) {
            Log::warning('Firebase credentials/config error', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (FailedToVerifyToken $e) {
            Log::warning('Firebase token verification failed', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Token Firebase tidak valid.', 'error' => $e->getMessage()], 422);
        }

        $uid = (string) $verifiedIdToken->claims()->get('sub');
        $email = (string) $verifiedIdToken->claims()->get('email');
        $tokenName = (string) ($verifiedIdToken->claims()->get('name') ?: Str::before($email, '@'));
        $name = (string) ($validated['name'] ?: $tokenName);
        $emailVerified = (bool) $verifiedIdToken->claims()->get('email_verified');

        if (! $uid || ! $email) {
            return response()->json(['message' => 'Data akun Firebase tidak lengkap.'], 422);
        }

        try {
            $userData = $this->upsertUser(
                uid: $uid,
                email: $email,
                name: $name,
                emailVerified: $emailVerified,
                role: $validated['role'],
            );

            if (($userData['role'] ?? null) === 'pasien') {
                $this->ensurePasienRecord($userData);
            }

            if (($userData['role'] ?? null) === 'dokter') {
                $this->doctorRepository->ensureDoctorRecord($userData);
                $userData = $this->doctorRepository->hydrateDoctorData($userData);
            }
        } catch (RuntimeException $e) {
            Log::warning('Firestore user sync failed', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected Firestore user sync failure', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Registrasi gagal saat menyimpan data pengguna.'], 500);
        }

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user_id' => $userData['id'] ?? null,
            'role' => $userData['role'] ?? $validated['role'],
            'redirect' => route('login-pasien'),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findExistingUser(string $uid, string $email): ?array
    {
        $byUid = $this->firestore->find(self::USERS_COLLECTION, $uid);
        if ($byUid) {
            return $byUid;
        }

        $byEmail = $this->firestore->where(self::USERS_COLLECTION, 'email', '=', $email, 1);

        return $byEmail[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function upsertUser(
        string $uid,
        string $email,
        string $name,
        bool $emailVerified,
        ?string $role = null,
    ): array {
        $normalizedRole = $this->normalizeRole($role);
        $existing = $this->findExistingUser($uid, $email);

        if (! $existing) {
            $payload = [
                'fullname' => $name,
                'role' => $normalizedRole,
                'email' => $email,
                'password' => null,
                'created_at' => now()->toIso8601String(),
                'update_at' => now()->toIso8601String(),
            ];

            $this->firestore->set(self::USERS_COLLECTION, $uid, $payload);

            return array_merge(['id' => $uid], $payload);
        }

        $existingRole = $this->normalizeRole($existing['role'] ?? null);
        if ($normalizedRole !== null && $existingRole !== null && $existingRole !== $normalizedRole) {
            throw new RuntimeException('Jenis akun tidak sesuai. Silakan login melalui halaman yang benar.');
        }

        $payload = [
            'email' => $email,
            'password' => $existing['password'] ?? null,
            'update_at' => now()->toIso8601String(),
        ];

        if ($name !== '') {
            $payload['fullname'] = $name;
        }

        if ($normalizedRole !== null) {
            $payload['role'] = $normalizedRole;
        }

        $this->firestore->update(self::USERS_COLLECTION, (string) $existing['id'], $payload);

        return array_merge($existing, $payload);
    }

    private function ensurePasienRecord(array $userData): void
    {
        $uid = (string) ($userData['id'] ?? '');

        if ($uid === '') {
            return;
        }

        $existingPasien = $this->firestore->find(self::PASIEN_COLLECTION, $uid);

        $payload = [
            'user_id' => $uid,
            'fullname' => $userData['fullname'] ?? $userData['name'] ?? '',
            'email' => $userData['email'] ?? '',
            'role' => 'pasien',
            'umur' => $existingPasien['umur'] ?? null,
            'weight' => $existingPasien['weight'] ?? null,
            'height' => $existingPasien['height'] ?? null,
            'blood_type' => $existingPasien['blood_type'] ?? '',
            'allergy_history' => $existingPasien['allergy_history'] ?? '',
            'address' => $existingPasien['address'] ?? '',
            'city' => $existingPasien['city'] ?? 'Bandung',
            'code_pos' => $existingPasien['code_pos'] ?? '',
            'country' => $existingPasien['country'] ?? 'Indonesia',
            'created_at' => $existingPasien['created_at'] ?? now()->toIso8601String(),
            'update_at' => now()->toIso8601String(),
        ];

        $this->firestore->set(self::PASIEN_COLLECTION, $uid, $payload);
    }

    private function normalizeRole(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $normalized = strtolower(trim($role));

        return in_array($normalized, self::VALID_ROLES, true) ? $normalized : null;
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveIdTokenForLogin(array $validated): string
    {
        $existingIdToken = $validated['id_token'] ?? null;
        if (is_string($existingIdToken) && $existingIdToken !== '') {
            return $existingIdToken;
        }

        $apiKey = (string) config('services.firebase.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('Konfigurasi Firebase API key belum tersedia.');
        }

        $response = Http::asJson()->post(
            sprintf('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=%s', $apiKey),
            [
                'email' => (string) ($validated['email'] ?? ''),
                'password' => (string) ($validated['password'] ?? ''),
                'returnSecureToken' => true,
            ],
        );

        $payload = $response->json();
        if (! $response->ok()) {
            $errorCode = strtoupper((string) data_get($payload, 'error.message', 'LOGIN_FAILED'));
            throw new RuntimeException($this->mapFirebaseLoginError($errorCode));
        }

        $idToken = data_get($payload, 'idToken');
        if (! is_string($idToken) || $idToken === '') {
            throw new RuntimeException('Login gagal. Token Firebase tidak ditemukan.');
        }

        return $idToken;
    }

    private function mapFirebaseLoginError(string $errorCode): string
    {
        return match (true) {
            str_contains($errorCode, 'EMAIL_NOT_FOUND'),
            str_contains($errorCode, 'INVALID_LOGIN_CREDENTIALS') => 'Email atau kata sandi salah.',
            str_contains($errorCode, 'INVALID_PASSWORD') => 'Kata sandi tidak sesuai.',
            str_contains($errorCode, 'INVALID_EMAIL') => 'Format email tidak valid.',
            str_contains($errorCode, 'TOO_MANY_ATTEMPTS_TRY_LATER') => 'Terlalu banyak percobaan. Coba beberapa saat lagi.',
            default => 'Login gagal. Silakan coba lagi.',
        };
    }

    private function loginErrorResponse(
        Request $request,
        string $message,
        int $status,
        ?string $error = null,
    ): JsonResponse|RedirectResponse {
        if (! $request->expectsJson() && ! $request->wantsJson()) {
            return back()
                ->withErrors(['email' => $message])
                ->withInput($request->except('password', 'id_token'));
        }

        $payload = ['message' => $message];
        if ($error !== null) {
            $payload['error'] = $error;
        }

        return response()->json($payload, $status);
    }

    /**
     * Tentukan URL redirect setelah login berdasarkan kelengkapan profil.
     * Dokter yang belum mengisi profil (belum ada data phone/age) diarahkan ke form profil.
     *
     * @param array<string, mixed> $userData
     */
    private function resolvePostLoginRedirect(array $userData): string
    {
        if (($userData['role'] ?? null) === 'pasien') {
            return route('pasien.beranda');
        }

        if (($userData['role'] ?? null) === 'dokter') {
            if (! DoctorProfile::hasPersonalData($userData)) {
                return route('doctor.profile-form');
            }

            if (! DoctorProfile::hasExpertiseData($userData)) {
                return route('doctor.profile.expertise');
            }

            if (! DoctorProfile::hasCertificationData($userData)) {
                return route('doctor.profile.certification');
            }

            return route('dashboard');
        }

        return route('login-pasien');
    }
}
