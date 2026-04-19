<?php

namespace App\Support;

class DoctorProfile
{
    private const PERSONAL_FIELDS = [
        'name',
        'phone',
        'age',
        'weight',
        'height',
        'gender',
        'country',
        'city',
        'postal_code',
    ];

    private const EXPERTISE_FIELDS = [
        'specialty',
        'started_practice_year',
        'education_institution',
        'bio',
    ];

    private const CERTIFICATION_FIELDS = [
        'certification1',
    ];

    /**
     * @param array<string, mixed> $userData
     */
    public static function hasPersonalData(array $userData): bool
    {
        if (! empty($userData['personal_data_completed'])) {
            return true;
        }

        foreach (self::PERSONAL_FIELDS as $field) {
            if (blank($userData[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $userData
     */
    public static function hasExpertiseData(array $userData): bool
    {
        if (! empty($userData['expertise_completed'])) {
            return true;
        }

        foreach (self::EXPERTISE_FIELDS as $field) {
            if (blank($userData[$field] ?? null)) {
                return false;
            }
        }

        $services = $userData['services'] ?? [];

        return is_array($services) && count($services) > 0;
    }

    /**
     * @param array<string, mixed> $userData
     */
    public static function hasCertificationData(array $userData): bool
    {
        if (! empty($userData['certification_completed'])) {
            return true;
        }

        foreach (self::CERTIFICATION_FIELDS as $field) {
            if (blank($userData[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $userData
     */
    public static function isComplete(array $userData): bool
    {
        if (($userData['role'] ?? null) !== 'dokter') {
            return true;
        }

        if (! empty($userData['profile_completed'])) {
            return true;
        }

        return self::hasPersonalData($userData) &&
               self::hasExpertiseData($userData) &&
               self::hasCertificationData($userData);
    }
}
