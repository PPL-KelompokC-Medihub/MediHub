<?php

namespace App\Auth;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class FirestoreUserProvider implements UserProvider
{
    private const COLLECTION = 'users';

    public function __construct(
        private FirestoreService $firestore,
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

        return new FirestoreUser($data);
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

        return new FirestoreUser($data);
    }

    /**
     * Update the remember token on the given user.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $this->firestore->update(self::COLLECTION, (string) $user->getAuthIdentifier(), [
            'remember_token' => $token,
        ]);
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

        return new FirestoreUser($results[0]);
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
