<?php

namespace App\Services;

use RuntimeException;

class MedihubFirestoreRepository
{
    private const USERS_COLLECTION = 'users';
    private const DOCTORS_COLLECTION = 'doctors';
    private const DOCTOR_SPECIALIZATIONS_COLLECTION = 'doctor_specializations';
    private const DOCTOR_CERTIFICATIONS_COLLECTION = 'doctor_certifications';
    private const DOCTOR_DOCUMENTS_COLLECTION = 'doctor_documents';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function findUser(string $userId): ?array
    {
        return $this->firestore->find(self::USERS_COLLECTION, $userId);
    }

    /**
     * @param array<string, mixed> $userData
     * @return array<string, mixed>
     */
    public function hydrateDoctorData(array $userData): array
    {
        if (($userData['role'] ?? null) !== 'dokter') {
            return $userData;
        }

        $doctor = $this->findDoctorByUserId((string) ($userData['id'] ?? ''));
        if (! $doctor) {
            return $userData;
        }

        $specialization = $this->findDoctorSpecialization((string) $doctor['id']);
        $certification = $this->findDoctorCertification((string) $doctor['id']);

        $mappedDoctor = [
            'doctor_id' => $doctor['id'],
            'age' => $doctor['umur'] ?? null,
            'weight' => $doctor['weight'] ?? null,
            'height' => $doctor['height'] ?? null,
            'gender' => $doctor['gender'] ?? null,
            'country' => $doctor['country'] ?? null,
            'city' => $doctor['city'] ?? null,
            'postal_code' => $doctor['code_pos'] ?? null,
        ];

        $mappedSpecialization = [];
        if ($specialization) {
            $services = $specialization['services'] ?? null;
            if (! is_array($services)) {
                $servicesString = trim((string) ($specialization['service'] ?? ''));
                $services = $servicesString === ''
                    ? []
                    : array_values(array_filter(array_map('trim', explode(',', $servicesString))));
            }

            $mappedSpecialization = [
                'specialty' => $specialization['main_specialization'] ?? null,
                'sub_specialty' => $specialization['sub_specialization'] ?? null,
                'started_practice_year' => $specialization['practice_year'] ?? null,
                'education_institution' => $specialization['academy'] ?? null,
                'services' => $services,
                'bio' => $specialization['short_biography'] ?? null,
            ];
        }

        $mappedCertification = [];
        if ($certification) {
            $mappedCertification = [
                'certification1' => $certification['certification1'] ?? null,
                'certification2' => $certification['certification2'] ?? null,
                'certification3' => $certification['certification3'] ?? null,
                'certification4' => $certification['certification4'] ?? null,
                'certification5' => $certification['certification5'] ?? null,
                'certification6' => $certification['certification6'] ?? null,
            ];
        }

        return array_merge($userData, $mappedDoctor, $mappedSpecialization, $mappedCertification);
    }

    /**
     * @param array<string, mixed> $userData
     */
    public function ensureDoctorRecord(array $userData): void
    {
        if (($userData['role'] ?? null) !== 'dokter') {
            return;
        }

        $userId = (string) ($userData['id'] ?? '');
        if ($userId === '') {
            return;
        }

        $doctor = $this->findDoctorByUserId($userId);
        if ($doctor) {
            return;
        }

        $this->firestore->add(self::DOCTORS_COLLECTION, [
            'user_id' => $userId,
            'email' => $userData['email'] ?? null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    public function updateDoctorPersonal(string $userId, array $validated): array
    {
        $user = $this->findUser($userId);
        if (! $user) {
            throw new RuntimeException('User dokter tidak ditemukan.');
        }

        $this->ensureDoctorRecord($user);
        $doctor = $this->findDoctorByUserId($userId);
        if (! $doctor) {
            throw new RuntimeException('Data dokter gagal dipersiapkan.');
        }

        $doctorPayload = [
            'user_id' => $userId,
            'umur' => (int) $validated['age'],
            'email' => $user['email'] ?? null,
            'weight' => (string) $validated['weight'],
            'height' => (string) $validated['height'],
            'gender' => $validated['gender'],
            'country' => $validated['country'],
            'city' => $validated['city'],
            'code_pos' => $validated['postal_code'],
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore->update(self::DOCTORS_COLLECTION, (string) $doctor['id'], $doctorPayload);

        $userPayload = [
            'name' => $validated['name'],
            'fullname' => $validated['name'],
            'email' => $user['email'] ?? null,
            'role' => 'dokter',
            'doctor_id' => (string) $doctor['id'],
            'personal_data_completed' => true,
            'personal_data_completed_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore->update(self::USERS_COLLECTION, $userId, $userPayload);

        $updatedUser = array_merge($user, $userPayload, $validated);
        $updatedUser['id'] = $userId;

        return $this->hydrateDoctorData($updatedUser);
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    public function updateDoctorExpertise(string $userId, array $validated): array
    {
        $user = $this->findUser($userId);
        if (! $user) {
            throw new RuntimeException('User dokter tidak ditemukan.');
        }

        $this->ensureDoctorRecord($user);
        $doctor = $this->findDoctorByUserId($userId);
        if (! $doctor) {
            throw new RuntimeException('Data dokter tidak ditemukan.');
        }

        $specialization = $this->findDoctorSpecialization((string) $doctor['id']);
        $specializationPayload = [
            'doctor_id' => (string) $doctor['id'],
            'main_specialization' => $validated['specialty'],
            'sub_specialization' => $validated['sub_specialty'] ?? null,
            'practice_year' => (string) $validated['started_practice_year'],
            'academy' => $validated['education_institution'],
            'service' => implode(', ', $validated['services']),
            'services' => $validated['services'],
            'short_biography' => $validated['bio'],
            'updated_at' => now()->toIso8601String(),
        ];

        if ($specialization) {
            $this->firestore->update(
                self::DOCTOR_SPECIALIZATIONS_COLLECTION,
                (string) $specialization['id'],
                $specializationPayload,
            );
        } else {
            $this->firestore->add(self::DOCTOR_SPECIALIZATIONS_COLLECTION, array_merge(
                $specializationPayload,
                ['created_at' => now()->toIso8601String()],
            ));
        }

        $userPayload = [
            'role' => 'dokter',
            'doctor_id' => (string) $doctor['id'],
            'expertise_completed' => true,
            'expertise_completed_at' => now()->toIso8601String(),
            'profile_completed' => ! empty($user['certification_completed']),
            'profile_completed_at' => ! empty($user['certification_completed']) ? now()->toIso8601String() : null,
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore->update(self::USERS_COLLECTION, $userId, $userPayload);

        $updatedUser = array_merge($user, $userPayload, $validated);
        $updatedUser['id'] = $userId;

        return $this->hydrateDoctorData($updatedUser);
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    public function updateDoctorCertification(string $userId, array $validated): array
    {
        $user = $this->findUser($userId);
        if (! $user) {
            throw new RuntimeException('User dokter tidak ditemukan.');
        }

        $this->ensureDoctorRecord($user);
        $doctor = $this->findDoctorByUserId($userId);
        if (! $doctor) {
            throw new RuntimeException('Data dokter tidak ditemukan.');
        }

        $certifications = array_values($validated['certifications'] ?? []);
        $certification = $this->findDoctorCertification((string) $doctor['id']);
        $certificationPayload = [
            'doctor_id' => (string) $doctor['id'],
            'certification1' => $certifications[0] ?? null,
            'certification2' => $certifications[1] ?? null,
            'certification3' => $certifications[2] ?? null,
            'certification4' => $certifications[3] ?? null,
            'certification5' => $certifications[4] ?? null,
            'certification6' => $certifications[5] ?? null,
            'updated_at' => now()->toIso8601String(),
        ];

        if ($certification) {
            $this->firestore->update(
                self::DOCTOR_CERTIFICATIONS_COLLECTION,
                (string) $certification['id'],
                $certificationPayload,
            );
        } else {
            $this->firestore->add(self::DOCTOR_CERTIFICATIONS_COLLECTION, array_merge(
                $certificationPayload,
                ['created_at' => now()->toIso8601String()],
            ));
        }

        $documents = $validated['documents'] ?? [];
        $document = $this->findDoctorDocument((string) $doctor['id']);
        $documentPayload = [
            'doctor_id' => (string) $doctor['id'],
            'STR' => $documents['STR'] ?? null,
            'SIP' => $documents['SIP'] ?? null,
            'ijazah_doctor' => $documents['ijazah_doctor'] ?? null,
            'KTP' => $documents['KTP'] ?? null,
            'profile_pict' => $documents['profile_pict'] ?? null,
            'updated_at' => now()->toIso8601String(),
        ];

        if ($document) {
            $this->firestore->update(self::DOCTOR_DOCUMENTS_COLLECTION, (string) $document['id'], $documentPayload);
        } else {
            $this->firestore->add(self::DOCTOR_DOCUMENTS_COLLECTION, array_merge(
                $documentPayload,
                ['created_at' => now()->toIso8601String()],
            ));
        }

        $userPayload = [
            'role' => 'dokter',
            'doctor_id' => (string) $doctor['id'],
            'certification_completed' => true,
            'certification_completed_at' => now()->toIso8601String(),
            'profile_completed' => true,
            'profile_completed_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore->update(self::USERS_COLLECTION, $userId, $userPayload);

        $updatedUser = array_merge($user, $userPayload, $validated);
        $updatedUser['id'] = $userId;

        return $this->hydrateDoctorData($updatedUser);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorByUserId(string $userId): ?array
    {
        if ($userId === '') {
            return null;
        }

        $results = $this->firestore->where(self::DOCTORS_COLLECTION, 'user_id', '=', $userId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorSpecialization(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_SPECIALIZATIONS_COLLECTION, 'doctor_id', '=', $doctorId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorCertification(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_CERTIFICATIONS_COLLECTION, 'doctor_id', '=', $doctorId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorDocument(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_DOCUMENTS_COLLECTION, 'doctor_id', '=', $doctorId, 1);

        return $results[0] ?? null;
    }
}
