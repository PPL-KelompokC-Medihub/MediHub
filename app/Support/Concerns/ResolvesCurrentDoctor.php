<?php

namespace App\Support\Concerns;

use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Support\Facades\Auth;

trait ResolvesCurrentDoctor
{
    private bool $hasResolvedCurrentDoctorData = false;

    /** @var array<string, mixed>|null */
    private ?array $resolvedCurrentDoctorData = null;

    private ?string $resolvedCurrentDoctorId = null;

    /** @var array<int, string>|null */
    private ?array $resolvedCurrentDoctorOwnerIds = null;

    /**
     * @return array<string, mixed>|null
     */
    protected function currentDoctorData(): ?array
    {
        if ($this->hasResolvedCurrentDoctorData) {
            return $this->resolvedCurrentDoctorData;
        }

        $this->hasResolvedCurrentDoctorData = true;

        $authUserData = $this->authenticatedUserData();
        if ($authUserData !== [] && ($this->canUseAuthenticatedUserData($authUserData) || ! $this->hasDoctorRepository())) {
            $this->resolvedCurrentDoctorData = $authUserData;

            return $this->resolvedCurrentDoctorData;
        }

        $userId = (string) ($authUserData['id'] ?? Auth::id());
        if ($userId === '' || ! $this->hasDoctorRepository()) {
            $this->resolvedCurrentDoctorData = $authUserData !== [] ? $authUserData : null;

            return $this->resolvedCurrentDoctorData;
        }

        try {
            $userData = $this->doctorRepository->findUser($userId);
            $this->resolvedCurrentDoctorData = $userData
                ? $this->doctorRepository->hydrateDoctorData($userData)
                : ($authUserData !== [] ? $authUserData : null);
        } catch (\Throwable) {
            $this->resolvedCurrentDoctorData = $authUserData !== [] ? $authUserData : null;
        }

        if ($this->resolvedCurrentDoctorData !== null) {
            $this->rememberCurrentDoctorData($this->resolvedCurrentDoctorData);
        }

        return $this->resolvedCurrentDoctorData;
    }

    protected function currentDoctorObject(): ?object
    {
        $data = $this->currentDoctorData();

        return $data ? (object) $data : null;
    }

    protected function currentDoctorId(): string
    {
        if ($this->resolvedCurrentDoctorId !== null) {
            return $this->resolvedCurrentDoctorId;
        }

        $doctorData = $this->currentDoctorData() ?? [];
        $doctorId = trim((string) ($doctorData['doctor_id'] ?? $doctorData['dokterid'] ?? ''));
        if ($doctorId !== '') {
            return $this->resolvedCurrentDoctorId = $doctorId;
        }

        $userId = (string) Auth::id();
        if ($userId !== '' && $this->hasFirestoreService()) {
            $doctor = $this->firestore->where('Dokter', 'usersId', '=', $userId, 1)[0] ?? null;
            $doctorId = trim((string) ($doctor['id'] ?? ''));
        }

        return $this->resolvedCurrentDoctorId = ($doctorId !== '' ? $doctorId : $userId);
    }

    /**
     * @return array<int, string>
     */
    protected function currentDoctorOwnerIds(): array
    {
        if ($this->resolvedCurrentDoctorOwnerIds !== null) {
            return $this->resolvedCurrentDoctorOwnerIds;
        }

        return $this->resolvedCurrentDoctorOwnerIds = array_values(array_unique(array_filter([
            $this->currentDoctorId(),
            (string) Auth::id(),
        ], fn (string $id): bool => $id !== '')));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function whereCurrentDoctorOwner(string $collection, string $field): array
    {
        if (! $this->hasFirestoreService()) {
            return [];
        }

        $documents = [];
        foreach (array_chunk($this->currentDoctorOwnerIds(), 30) as $ids) {
            if ($ids === []) {
                continue;
            }

            $results = count($ids) === 1
                ? $this->firestore->where($collection, $field, '=', $ids[0])
                : $this->firestore->where($collection, $field, 'in', $ids);

            foreach ($results as $document) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * Firestore reads are remote HTTP calls; prefer the canonical field and
     * only hit legacy aliases when that field has no rows for this doctor.
     *
     * @param array<int, string> $fields
     * @return array<int, array<string, mixed>>
     */
    protected function whereCurrentDoctorOwnerByAnyField(string $collection, array $fields): array
    {
        foreach ($fields as $field) {
            $documents = $this->whereCurrentDoctorOwner($collection, $field);

            if ($documents !== []) {
                return $documents;
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function authenticatedUserData(): array
    {
        $user = Auth::user();

        if (! $user || ! method_exists($user, 'getAttributes')) {
            return [];
        }

        $data = $user->getAttributes();

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function canUseAuthenticatedUserData(array $userData): bool
    {
        $role = strtolower(trim((string) ($userData['role'] ?? '')));

        if ($role !== 'dokter') {
            return true;
        }

        if (($userData['firestore_unavailable'] ?? false) === true) {
            return true;
        }

        return trim((string) ($userData['doctor_id'] ?? $userData['dokterid'] ?? '')) !== '';
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function rememberCurrentDoctorData(array $userData): void
    {
        if (! request()->hasSession()) {
            return;
        }

        unset(
            $userData['password'],
            $userData['remember_token'],
            $userData['api_token'],
        );

        if (! isset($userData['name'])) {
            $userData['name'] = $userData['fullname'] ?? null;
        }

        request()->session()->put('medihub_user', $userData);
        request()->session()->put('medihub_user_role', $userData['role'] ?? null);
    }

    private function hasFirestoreService(): bool
    {
        return property_exists($this, 'firestore') && $this->firestore instanceof FirestoreService;
    }

    private function hasDoctorRepository(): bool
    {
        return property_exists($this, 'doctorRepository')
            && $this->doctorRepository instanceof MedihubFirestoreRepository;
    }
}
