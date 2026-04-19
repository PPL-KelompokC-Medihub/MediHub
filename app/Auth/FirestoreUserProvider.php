<?php

namespace App\Auth;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class FirestoreUserProvider implements UserProvider
{
    private const COLLECTION = 'Users';

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
        $data = $this->firestore->find(self::COLLECTION, (string) $identifier);

        if (! $data) {
            return null;
        }

        return new FirestoreUser($this->medihubRepository->hydrateDoctorData($data));
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

        return new FirestoreUser($this->medihubRepository->hydrateDoctorData($data));
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

        return new FirestoreUser($this->medihubRepository->hydrateDoctorData($results[0]));
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
}
