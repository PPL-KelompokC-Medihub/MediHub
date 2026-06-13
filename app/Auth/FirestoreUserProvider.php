<?php

namespace App\Auth;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Throwable;

class FirestoreUserProvider implements UserProvider
{
    private const COLLECTION = 'Users';
    private const SESSION_USER_KEY = 'medihub_user';

    public function __construct(
        private FirestoreService $firestore,
        private MedihubFirestoreRepository $medihubRepository,
    ) {
    }

    /**
     * Retrieve a user by their unique identifier (Firestore document ID).
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $identifier = (string) $identifier;
        $sessionUser = $this->retrieveFromSession($identifier);

        if ($sessionUser) {
            return $sessionUser;
        }

        $data = null;

        try {
            $data = $this->firestore->find(self::COLLECTION, $identifier);
        } catch (Throwable) {
            $data = null;
        }

        if (! $data) {
            return null;
        }

        return $this->createUser($data, remember: true);
    }

    /**
     * Retrieve a user by their remember token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $data = $this->firestore->find(self::COLLECTION, (string) $identifier);

        if (! $data || ($data['remember_token'] ?? null) !== $token) {
            return null;
        }

        return $this->createUser($data, remember: true);
    }

    /**
     * Update the remember token on the given user.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Firebase handles persistent auth; Users schema intentionally has no remember_token field.
    }

    /**
     * Retrieve a user by credentials (email).
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['email'])) {
            return null;
        }

        $results = $this->firestore->where(self::COLLECTION, 'email', '=', (string) $credentials['email'], 1);

        if (empty($results)) {
            return null;
        }

        return $this->createUser($results[0], remember: true);
    }

    /**
     * Validate a user against the given credentials.
     * (Not used — Firebase handles password validation client-side)
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return false;
    }

    /**
     * Rehash the user's password if needed.
     * (Not used — Firebase handles passwords)
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // No-op: Firebase manages passwords
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUser(array $data, bool $remember = false): FirestoreUser
    {
        $userData = $this->medihubRepository->hydrateDoctorData($data);

        if ($remember) {
            $this->rememberSessionUser($userData);
        }

        return new FirestoreUser($userData);
    }

    private function retrieveFromSession(string $identifier): ?Authenticatable
    {
        $sessionUser = $this->sessionUserData();

        if (! $sessionUser || (string) ($sessionUser['id'] ?? '') !== $identifier) {
            return null;
        }

        return new FirestoreUser($sessionUser);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sessionUserData(): ?array
    {
        if (! app()->bound('request') || ! request()->hasSession()) {
            return null;
        }

        $sessionUser = request()->session()->get(self::SESSION_USER_KEY);

        return is_array($sessionUser) ? $sessionUser : null;
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function rememberSessionUser(array $userData): void
    {
        if (! app()->bound('request') || ! request()->hasSession()) {
            return;
        }

        request()->session()->put(self::SESSION_USER_KEY, $this->sessionSafeUserData($userData));
        request()->session()->put('medihub_user_role', $userData['role'] ?? null);
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
}
