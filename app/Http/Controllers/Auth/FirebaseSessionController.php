<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Dokter\DokterProfile;
use App\Http\Controllers\Controller;
use App\Models\FirestoreUser;
use App\Models\User;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use RuntimeException;

/**
 * Bridge antara Firebase Authentication (frontend) dan session Laravel.
 *
 * Frontend (resources/js/auth/* dan resources/js/auth-pasien/*) sign-in
 * pakai Firebase JS SDK lalu mengirim id_token ke endpoint ini. Token
 * diverifikasi pakai Firebase Admin SDK, lalu user di-upsert ke
 * Firestore collection "Users" dan akhirnya di-login ke session web.
 */
class FirebaseSessionController extends Controller
{
    private const USERS_COLLECTION = 'Users';
    private const VALID_ROLES = ['dokter', 'pasien'];

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $doctorRepository,
    ) {}

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $timings = ['start' => microtime(true)];

        Log::info('FirebaseSessionController.login called', [
            'method' => $request->method(),
            'path' => $request->path(),
            'role' => $request->json('role'),
            'email' => $request->json('email'),
            'has_id_token' => $request->json('id_token') !== null,
        ]);

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

        // Try local database authentication as fallback for development/testing
        if (isset($validated['email']) && isset($validated['password']) && empty($validated['id_token'])) {
            $localAuth = $this->attemptLocalAuth($request, $validated);
            if ($localAuth) {
                return $localAuth;
            }
        }

        try {
            $idToken = $this->resolveIdTokenForLogin($validated);
            $timings['resolved_id_token'] = microtime(true);
            $verifiedIdToken = $this->firestore->auth()->verifyIdToken($idToken);
            $timings['verified_id_token'] = microtime(true);
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
        $name = (string) ($verifiedIdToken->claims()->get('name') ?: Str::before($email, '@'));
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
            $userData = $this->cachedAuthenticatedUserData($uid, $email, $validated['role'] ?? null);

            if ($userData) {
                $timings['loaded_user_cache'] = microtime(true);
            } else {
                $userData = $this->upsertUser(
                    uid: $uid,
                    email: $email,
                    name: $name,
                    emailVerified: $emailVerified,
                    role: $validated['role'] ?? null,
                );
                $timings['upserted_user'] = microtime(true);
                $this->doctorRepository->ensureDoctorRecord($userData);
                $timings['ensured_doctor'] = microtime(true);
                $userData = $this->doctorRepository->hydrateDoctorData($userData);
                $timings['hydrated_doctor'] = microtime(true);
                $this->cacheAuthenticatedUserData($userData);
            }
        } catch (RuntimeException $e) {
            Log::warning('Firestore user sync failed', ['message' => $e->getMessage()]);

            if (! $this->canUseSessionOnlyLoginFallback($e)) {
                return $this->loginErrorResponse($request, $e->getMessage(), 422);
            }

            $userData = $this->firebaseSessionOnlyUserData(
                uid: $uid,
                email: $email,
                name: $name,
                emailVerified: $emailVerified,
                role: $validated['role'] ?? null,
            );

            Log::warning('Continuing with session-only Firebase login because Firestore is unavailable.', [
                'user_id' => $uid,
                'email' => $email,
                'role' => $userData['role'] ?? null,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected Firestore user sync failure', ['message' => $e->getMessage()]);
            return $this->loginErrorResponse($request, 'Login gagal saat menyimpan sesi pengguna.', 500);
        }

        $user = new FirestoreUser($userData);

        // Firestore-backed auth tidak menyimpan password hash lokal,
        // jadi "remember me" cookie flow tidak dipakai di sini.
        Auth::login($user, false);
        $request->session()->regenerate();
        $this->rememberAuthenticatedUser($request, $userData);
        $request->session()->save();
        $timings['saved_session'] = microtime(true);

        // Dokter yang belum mengisi profil dikirim ke form profil
        $redirectUrl = $this->resolvePostLoginRedirect($userData);
        $timings['resolved_redirect'] = microtime(true);
        $this->logLoginTimings($timings);

        if (! $request->expectsJson() && ! $request->wantsJson()) {
            return redirect()->intended($redirectUrl);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * @param array<string, float> $timings
     */
    private function logLoginTimings(array $timings): void
    {
        if (! config('app.debug')) {
            return;
        }

        $previous = $timings['start'] ?? microtime(true);
        $segments = [];

        foreach ($timings as $label => $time) {
            if ($label === 'start') {
                continue;
            }

            $segments[$label] = (int) round(($time - $previous) * 1000);
            $previous = $time;
        }

        $segments['total'] = (int) round(((end($timings) ?: microtime(true)) - $timings['start']) * 1000);

        Log::debug('Firebase session login timings', $segments);
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
            $this->doctorRepository->ensureDoctorRecord($userData);
            $userData = $this->doctorRepository->hydrateDoctorData($userData);
            $this->cacheAuthenticatedUserData($userData);
        } catch (RuntimeException $e) {
            Log::warning('Firestore user sync failed', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Unexpected Firestore user sync failure', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Registrasi gagal saat menyimpan data pengguna.'], 500);
        }

        // Auto-login setelah registrasi
        $user = new FirestoreUser($userData);
        Auth::login($user, false);
        $request->session()->regenerate();
        $this->rememberAuthenticatedUser($request, $userData, $validated['role']);
        $request->session()->save();

        // Tentukan redirect berdasarkan kelengkapan profil
        $redirectUrl = $this->resolvePostLoginRedirect($userData);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user_id' => $userData['id'] ?? null,
            'role' => $userData['role'] ?? $validated['role'],
            'redirect' => $redirectUrl,
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
            Log::warning('Firebase user attempted login from the wrong role page.', [
                'user_id' => $existing['id'] ?? $uid,
                'email' => $email,
                'existing_role' => $existingRole,
                'requested_role' => $normalizedRole,
            ]);

            throw new RuntimeException('Jenis akun tidak sesuai. Silakan login melalui halaman yang benar.');
        }

        $payload = [
            'fullname' => $name ?: ($existing['fullname'] ?? $existing['name'] ?? Str::before($email, '@')),
            'email' => $email,
            'password' => $existing['password'] ?? null,
        ];

        if ($existingRole === null && $normalizedRole !== null) {
            $payload['role'] = $normalizedRole;
        }

        $changedPayload = array_filter(
            $payload,
            fn (mixed $value, string $key): bool => ($existing[$key] ?? null) !== $value,
            ARRAY_FILTER_USE_BOTH,
        );

        if ($changedPayload !== []) {
            $changedPayload['update_at'] = now()->toIso8601String();
            $this->firestore->update(self::USERS_COLLECTION, (string) $existing['id'], $changedPayload);
        }

        return array_merge($existing, $payload);
    }

    private function normalizeRole(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $normalized = strtolower(trim($role));

        return in_array($normalized, self::VALID_ROLES, true) ? $normalized : null;
    }

    private function canUseSessionOnlyLoginFallback(RuntimeException $exception): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            return false;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'firestore')
            || str_contains($message, 'dokumen')
            || str_contains($message, 'document');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cachedAuthenticatedUserData(string $uid, string $email, ?string $requestedRole): ?array
    {
        try {
            $cached = Cache::get($this->authenticatedUserCacheKey($uid));
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($cached) || (string) ($cached['id'] ?? '') !== $uid || (string) ($cached['email'] ?? '') !== $email) {
            return null;
        }

        $cachedRole = $this->normalizeRole($cached['role'] ?? null);
        $normalizedRole = $this->normalizeRole($requestedRole);

        if ($normalizedRole !== null && $cachedRole !== null && $cachedRole !== $normalizedRole) {
            throw new RuntimeException('Jenis akun tidak sesuai. Silakan login melalui halaman yang benar.');
        }

        return $cached;
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function cacheAuthenticatedUserData(array $userData): void
    {
        $uid = (string) ($userData['id'] ?? '');
        if ($uid === '') {
            return;
        }

        try {
            Cache::put($this->authenticatedUserCacheKey($uid), $this->sessionSafeUserData($userData), now()->addMinutes(30));
        } catch (\Throwable) {
            // Cache only avoids repeated Firestore reads; login remains valid without it.
        }
    }

    private function authenticatedUserCacheKey(string $uid): string
    {
        return 'firebase_authenticated_user:'.sha1($uid);
    }

    /**
     * @return array<string, mixed>
     */
    private function firebaseSessionOnlyUserData(
        string $uid,
        string $email,
        string $name,
        bool $emailVerified,
        ?string $role,
    ): array {
        $displayName = $name !== '' ? $name : Str::before($email, '@');

        return [
            'id' => $uid,
            'fullname' => $displayName,
            'name' => $displayName,
            'email' => $email,
            'role' => $this->normalizeRole($role) ?? 'pasien',
            'email_verified' => $emailVerified,
            'firestore_unavailable' => true,
        ];
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

        $response = Http::asJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'curl' => [
                    CURLOPT_RESOLVE => \App\Services\DnsResolver::getDnsResolveMapping()
                ]
            ])
            ->post(
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
     * Dokter yang belum mengisi profil diarahkan ke form profil.
     *
     * @param array<string, mixed> $userData
     */
    private function resolvePostLoginRedirect(array $userData): string
    {
        if (($userData['role'] ?? null) !== 'dokter') {
            return route('pasien.beranda');
        }

        if (! DokterProfile::hasPersonalData($userData)) {
            return route('dokter.profile.personal');
        }

        if (! DokterProfile::hasExpertiseData($userData)) {
            return route('dokter.profile.expertise');
        }

        if (! DokterProfile::hasCertificationData($userData)) {
            return route('dokter.profile.certification');
        }

        return route('dokter.dashboard');
    }

    /**
     * Attempt local database authentication (for development/testing without Firebase)
     */
    private function attemptLocalAuth(Request $request, array $validated): JsonResponse|RedirectResponse|null
    {
        $email = $validated['email'] ?? null;
        $password = $validated['password'] ?? null;

        if (! $email || ! $password) {
            return null;
        }

        try {
            $user = User::where('email', $email)->first();
        } catch (\Throwable $e) {
            Log::warning('Local database auth unavailable; continuing with Firebase auth.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $user || ! password_verify($password, $user->password)) {
            return null;
        }

        $userData = $this->localUserData($user);
        $sessionUser = new FirestoreUser($userData);

        // Local auth success
        Auth::login($sessionUser, false);
        $request->session()->regenerate();
        $this->rememberAuthenticatedUser($request, $userData);
        $request->session()->save();

        $redirectUrl = $userData['role'] === 'dokter' ? route('dokter.dashboard') : route('pasien.beranda');

        if (! $request->expectsJson() && ! $request->wantsJson()) {
            return redirect()->intended($redirectUrl);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function rememberAuthenticatedUser(Request $request, array $userData, ?string $fallbackRole = null): void
    {
        $sessionUser = $this->sessionSafeUserData($userData);

        $request->session()->put('medihub_user', $sessionUser);
        $request->session()->put('medihub_user_role', $sessionUser['role'] ?? $fallbackRole);
    }

    /**
     * @param array<string, mixed> $userData
     * @return array<string, mixed>
     */
    private function sessionSafeUserData(array $userData): array
    {
        unset(
            $userData['password'],
            $userData['remember_token'],
            $userData['api_token'],
        );

        if (! isset($userData['name'])) {
            $userData['name'] = $userData['fullname'] ?? null;
        }

        return $userData;
    }

    /**
     * @return array<string, mixed>
     */
    private function localUserData(User $user): array
    {
        return [
            'id' => (string) $user->getAuthIdentifier(),
            'fullname' => $user->name,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $this->normalizeRole((string) $user->role) ?? 'pasien',
            'email_verified' => true,
        ];
    }
}
