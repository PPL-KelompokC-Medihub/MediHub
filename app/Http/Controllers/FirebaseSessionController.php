<?php

namespace App\Http\Controllers;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use RuntimeException;

class FirebaseSessionController extends Controller
{
    private const USERS_COLLECTION = 'users';
    private const VALID_ROLES = ['dokter', 'pasien'];

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:dokter,pasien'],
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
        $name = (string) ($verifiedIdToken->claims()->get('name') ?: Str::before($email, '@'));
        $emailVerified = (bool) $verifiedIdToken->claims()->get('email_verified');

        if (! $uid || ! $email) {
            return response()->json(['message' => 'Data akun Firebase tidak lengkap.'], 422);
        }

        if (! $emailVerified) {
            return response()->json(['message' => 'Akun belum diverifikasi. Silakan verifikasi email terlebih dahulu sebelum login.'], 403);
        }

        $userData = $this->upsertUser(
            uid: $uid,
            email: $email,
            name: $name,
            emailVerified: $emailVerified,
            role: $validated['role'] ?? null,
        );

        $user = new FirestoreUser($userData);

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:dokter,pasien'],
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

        $user = $this->upsertUser(
            uid: $uid,
            email: $email,
            name: $name,
            emailVerified: $emailVerified,
            role: $validated['role'],
        );

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan login.',
            'user_id' => $user['id'] ?? null,
            'role' => $user['role'] ?? $validated['role'],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findExistingUser(string $uid, string $email): ?array
    {
        $byUid = $this->firestore->where(self::USERS_COLLECTION, 'firebase_uid', '=', $uid, 1);
        if (! empty($byUid[0])) {
            return $byUid[0];
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
                'name' => $name,
                'email' => $email,
                'firebase_uid' => $uid,
                'email_verified' => $emailVerified,
            ];

            if ($normalizedRole !== null) {
                $payload['role'] = $normalizedRole;
            }

            return $this->firestore->add(self::USERS_COLLECTION, $payload);
        }

        $payload = [
            'name' => $name ?: ($existing['name'] ?? Str::before($email, '@')),
            'email' => $email,
            'firebase_uid' => $uid,
            'email_verified' => $emailVerified,
        ];

        if ($normalizedRole !== null) {
            $payload['role'] = $normalizedRole;
        }

        $this->firestore->update(self::USERS_COLLECTION, (string) $existing['id'], $payload);

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
}
