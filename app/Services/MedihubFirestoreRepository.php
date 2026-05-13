<?php

namespace App\Services;

use RuntimeException;

class MedihubFirestoreRepository
{
    private const USERS_COLLECTION = 'Users';
    private const DOCTORS_COLLECTION = 'Dokter';
    private const DOCTOR_SPECIALIZATIONS_COLLECTION = 'Dokter_spesialisasi';
    private const DOCTOR_CERTIFICATIONS_COLLECTION = 'Dokter_sertifikasi';
    private const DOCTOR_DOCUMENTS_COLLECTION = 'Dokter_dokumen';

    public function __construct(
        private FirestoreService $firestore,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function findUser(string $userId): ?array
    {
        $user = $this->firestore->find(self::USERS_COLLECTION, $userId);

        return $user ? $this->withUserAliases($user) : null;
    }

    /**
     * @param array<string, mixed> $userData
     * @return array<string, mixed>
     */
    public function hydrateDoctorData(array $userData): array
    {
        $userData = $this->withUserAliases($userData);

        if (($userData['role'] ?? null) !== 'dokter') {
            return $userData;
        }

        $doctor = $this->findDoctorByUserId((string) ($userData['id'] ?? ''));
        if (! $doctor) {
            return $userData;
        }

        $specialization = $this->findDoctorSpecialization((string) $doctor['id']);
        $certification = $this->findDoctorCertification((string) $doctor['id']);
        $documents = $this->findDoctorDocument((string) $doctor['id']);

        $mappedDoctor = [
            'doctor_id' => $doctor['id'],
            'age' => $doctor['umur'] ?? null,
            'phone' => $doctor['numPhone'] ?? null,
            'weight' => $doctor['weight'] ?? null,
            'height' => $doctor['height'] ?? null,
            'gender' => $doctor['gender'] ?? null,
            'country' => $doctor['country'] ?? null,
            'city' => $doctor['city'] ?? null,
            'postal_code' => $doctor['codePos'] ?? null,
            'profile_pict' => $documents['profile_pict'] ?? null,
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
                'certification1' => $certification['sertification1'] ?? null,
                'certification2' => $certification['sertification2'] ?? null,
                'certification3' => $certification['sertification3'] ?? null,
                'certification4' => $certification['sertification4'] ?? null,
                'certification5' => $certification['sertification5'] ?? null,
                'certification6' => $certification['sertification6'] ?? null,
            ];
        }

        $mappedDocuments = [];
        if ($documents) {
            $mappedDocuments = [
                'STR' => $documents['STR'] ?? null,
                'SIP' => $documents['SIP'] ?? null,
                'ijazah_doctor' => $documents['ijazah_doctor'] ?? null,
                'KTP' => $documents['KTP'] ?? null,
            ];
        }

        return array_merge($userData, $mappedDoctor, $mappedSpecialization, $mappedCertification, $mappedDocuments);
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
            'usersId' => $userId,
            'email' => $userData['email'] ?? null,
            'numPhone' => null,
            'created_at' => now()->toIso8601String(),
            'update_at' => now()->toIso8601String(),
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
            'usersId' => $userId,
            'umur' => (int) $validated['age'],
            'email' => $user['email'] ?? null,
            'numPhone' => $this->normalizePhoneNumber($validated['phone']),
            'weight' => (string) $validated['weight'],
            'height' => (string) $validated['height'],
            'gender' => $validated['gender'],
            'country' => $validated['country'],
            'city' => $validated['city'],
            'codePos' => $validated['postal_code'],
            'update_at' => now()->toIso8601String(),
        ];
        if (isset($validated['profile_pict']) && !empty($validated['profile_pict'])) {
            $doctorPayload['profile_pict'] = $validated['profile_pict'];
        }

        $this->firestore->update(self::DOCTORS_COLLECTION, (string) $doctor['id'], $doctorPayload);

        $userPayload = [
            'fullname' => $validated['name'],
            'email' => $user['email'] ?? null,
            'role' => 'dokter',
            'update_at' => now()->toIso8601String(),
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
            'dokterid' => (string) $doctor['id'],
            'main_specialization' => $validated['specialty'],
            'sub_specialization' => $validated['sub_specialty'] ?? null,
            'practice_year' => (string) $validated['started_practice_year'],
            'academy' => $validated['education_institution'],
            'service' => implode(', ', $validated['services']),
            'short_biography' => $validated['bio'],
            'update_at' => now()->toIso8601String(),
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
            'update_at' => now()->toIso8601String(),
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
            'dokterid' => (string) $doctor['id'],
            'sertification1' => $certifications[0] ?? null,
            'sertification2' => $certifications[1] ?? null,
            'sertification3' => $certifications[2] ?? null,
            'sertification4' => $certifications[3] ?? null,
            'sertification5' => $certifications[4] ?? null,
            'sertification6' => $certifications[5] ?? null,
            'update_at' => now()->toIso8601String(),
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
            'dokterid' => (string) $doctor['id'],
            'STR' => $documents['STR'] ?? null,
            'SIP' => $documents['SIP'] ?? null,
            'ijazah_doctor' => $documents['ijazah_doctor'] ?? null,
            'KTP' => $documents['KTP'] ?? null,
            'profile_pict' => $documents['profile_pict'] ?? null,
            'update_at' => now()->toIso8601String(),
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
            'update_at' => now()->toIso8601String(),
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

        $results = $this->firestore->where(self::DOCTORS_COLLECTION, 'usersId', '=', $userId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorSpecialization(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_SPECIALIZATIONS_COLLECTION, 'dokterid', '=', $doctorId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorCertification(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_CERTIFICATIONS_COLLECTION, 'dokterid', '=', $doctorId, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDoctorDocument(string $doctorId): ?array
    {
        $results = $this->firestore->where(self::DOCTOR_DOCUMENTS_COLLECTION, 'dokterid', '=', $doctorId, 1);

        return $results[0] ?? null;
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function withUserAliases(array $user): array
    {
        if (! isset($user['name'])) {
            $user['name'] = $user['fullname'] ?? null;
        }

        return $user;
    }

    private function normalizePhoneNumber(string $phone): int
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '0';

        return (int) $digits;
    }

    public function deleteAppointment(string $appointmentId): void
    {
        $this->firestore->delete('appointments', $appointmentId);
    }


}
