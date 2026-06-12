<?php

namespace Tests\Browser\PBI_14_18;

use App\Models\User;
use App\Services\FirestoreService;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

abstract class Pbi14To18DuskTestCase extends DuskTestCase
{
    /** @var array<int, array{collection: string, id: string}> */
    private array $createdDocuments = [];

    /** @var array<int, string> */
    private array $appointmentsForNoteCleanup = [];

    /** @var array<int, array{patient_id: string, title: string, since: string}> */
    private array $notificationCleanupRules = [];

    protected function tearDown(): void
    {
        $this->cleanupFirestoreDocuments();

        parent::tearDown();
    }

    protected function appUrl(string $path): string
    {
        $baseUrl = rtrim((string) (env('DUSK_APP_URL') ?: 'http://127.0.0.1:8014'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }

    protected function loginAsPatient(Browser $browser, ?string $email = null, ?string $password = null): void
    {
        $browser->visit($this->appUrl('/logout'))
            ->visit($this->appUrl('/login-pasien'))
            ->waitFor('@login-email', 10)
            ->type('@login-email', $email ?? $this->patientEmail())
            ->type('@login-password', $password ?? $this->patientPassword())
            ->click('@login-button')
            ->waitForLocation('/pasien/beranda', 20)
            ->assertPathIs('/pasien/beranda');
    }

    protected function loginAsDoctor(Browser $browser, ?string $email = null, ?string $password = null): void
    {
        $browser->visit($this->appUrl('/logout'))
            ->visit($this->appUrl('/login-dokter'))
            ->waitFor('@login-email', 10)
            ->type('@login-email', $email ?? $this->doctorEmail())
            ->type('@login-password', $password ?? $this->doctorPassword())
            ->click('@login-button')
            ->waitForLocation('/dokter/dashboard', 20)
            ->assertPathIs('/dokter/dashboard');
    }

    protected function patientEmail(): string
    {
        return (string) env('DUSK_PATIENT_EMAIL', 'pasien.dummy@medihub.test');
    }

    protected function patientPassword(): string
    {
        return (string) env('DUSK_PATIENT_PASSWORD', 'Password123!');
    }

    protected function doctorEmail(): string
    {
        return (string) env('DUSK_DOCTOR_EMAIL', 'dokter.dummy@medihub.test');
    }

    protected function doctorPassword(): string
    {
        return (string) env('DUSK_DOCTOR_PASSWORD', 'Password123!');
    }

    /** @return array{id: string, email: string, name: string} */
    protected function patientIdentity(?string $email = null): array
    {
        $email ??= $this->patientEmail();
        $envUid = trim((string) env('DUSK_PATIENT_UID', ''));

        if ($envUid === '' && $email === 'pasien.dummy@medihub.test') {
            $envUid = '1OYSnwaa06bQmBOUBvx4JYHosHm2';
        }

        if ($envUid !== '') {
            return ['id' => $envUid, 'email' => $email, 'name' => 'Pasien Dummy'];
        }

        return $this->userIdentity($email, 'pasien');
    }

    /** @return array{id: string, user_id: string, email: string, name: string} */
    protected function doctorIdentity(?string $email = null): array
    {
        $email ??= $this->doctorEmail();
        $envDoctorId = trim((string) env('DUSK_DOCTOR_ID', ''));
        $envDoctorUid = trim((string) env('DUSK_DOCTOR_UID', ''));

        if ($envDoctorId === '' && $envDoctorUid === '' && $email === 'dokter.dummy@medihub.test') {
            $envDoctorId = 'dokter-dummy-profile';
            $envDoctorUid = 'dokter-dummy-medihub-test';
        }

        if ($envDoctorId !== '') {
            return [
                'id' => $envDoctorId,
                'user_id' => $envDoctorUid !== '' ? $envDoctorUid : $envDoctorId,
                'email' => $email,
                'name' => 'Dokter Dummy MediHub',
            ];
        }

        $user = $this->userIdentity($email, 'dokter');
        $doctor = $this->doctorRecordForUser($user['id']);

        return [
            'id' => (string) ($doctor['id'] ?? $user['id']),
            'user_id' => $user['id'],
            'email' => $email,
            'name' => $user['name'],
        ];
    }

    /** @return array{id: string, email: string, name: string} */
    protected function userIdentity(string $email, string $role): array
    {
        try {
            $localUser = User::where('email', $email)->first();
            if ($localUser && strtolower((string) $localUser->role) === $role) {
                return [
                    'id' => (string) $localUser->id,
                    'email' => (string) $localUser->email,
                    'name' => (string) ($localUser->name ?? $localUser->fullname ?? 'Dusk User'),
                ];
            }
        } catch (\Throwable) {
            // Some Dusk environments authenticate only via Firebase/Firestore.
        }

        foreach ($this->firestore()->where('Users', 'email', '=', $email) as $user) {
            if (strtolower((string) ($user['role'] ?? '')) === $role) {
                return [
                    'id' => (string) ($user['id'] ?? ''),
                    'email' => $email,
                    'name' => (string) ($user['fullname'] ?? $user['name'] ?? 'Dusk User'),
                ];
            }
        }

        $this->markTestSkipped("Tidak menemukan akun {$role} untuk {$email}. Set env DUSK_".strtoupper($role)."_UID atau gunakan akun test yang ada.");
    }

    /** @return array<string, mixed>|null */
    protected function doctorRecordForUser(string $userId): ?array
    {
        return $this->firestore()->where('Dokter', 'usersId', '=', $userId, 1)[0] ?? null;
    }

    /** @param array<string, mixed> $overrides */
    protected function createPatientAppointment(array $patient, array $overrides = []): array
    {
        $doctor = $this->bestAvailableDoctor();

        return $this->createAppointment(array_merge([
            'patient_id' => $patient['id'],
            'user_uid' => $patient['id'],
            'patient_email' => $patient['email'],
            'patient_name' => $patient['name'] ?? 'Dusk Patient',
            'doctor_id' => $doctor['id'],
            'dokterid' => $doctor['id'],
            'appointment_date' => Carbon::now()->addDays(3)->toDateString(),
            'appointment_time_start' => '10:00',
            'appointment_time_end' => '10:30',
            'appointment_time' => '10:00',
            'schedule_time_range' => '10:00 - 10:30',
            'queue_number' => random_int(70, 98),
            'status' => 'Menunggu',
            'complaint' => 'Keluhan automation PBI 14-18',
            'cancellation_reason' => null,
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    protected function createDoctorAppointment(array $doctor, array $patient, array $overrides = []): array
    {
        return $this->createAppointment(array_merge([
            'patient_id' => $patient['id'],
            'user_uid' => $patient['id'],
            'patient_email' => $patient['email'],
            'patient_name' => $patient['name'] ?? 'Dusk Patient',
            'doctor_id' => $doctor['id'],
            'dokterid' => $doctor['id'],
            'appointment_date' => Carbon::now()->subDay()->toDateString(),
            'appointment_time_start' => '09:00',
            'appointment_time_end' => '09:30',
            'appointment_time' => '09:00',
            'schedule_time_range' => '09:00 - 09:30',
            'queue_number' => random_int(20, 69),
            'status' => 'Menunggu',
            'complaint' => 'Keluhan automation PBI 17-18',
            'cancellation_reason' => null,
        ], $overrides));
    }

    /** @param array<string, mixed> $data */
    protected function createAppointment(array $data): array
    {
        $appointment = $this->firestore()->add('BuatJadwalTemu', $data);
        $this->rememberDocument('BuatJadwalTemu', (string) $appointment['id']);
        $this->appointmentsForNoteCleanup[] = (string) $appointment['id'];

        return $appointment;
    }

    /** @param array<string, mixed> $patient */
    protected function createNotification(array $patient, string $title, string $message, Carbon $createdAt): array
    {
        $notification = $this->firestore()->add('Notifications', [
            'patient_id' => $patient['id'],
            'title' => $title,
            'message' => $message,
            'type' => 'dusk_test',
            'read' => false,
        ]);

        $this->firestore()->update('Notifications', (string) $notification['id'], [
            'created_at' => $createdAt->toIso8601String(),
        ]);

        $this->rememberDocument('Notifications', (string) $notification['id']);

        return array_merge($notification, ['created_at' => $createdAt->toIso8601String()]);
    }

    /** @param array<string, mixed> $data */
    protected function createMedicalNote(array $data): array
    {
        $note = $this->firestore()->add('CatatanMedis', $data);
        $this->rememberDocument('CatatanMedis', (string) $note['id']);

        return $note;
    }

    protected function recordNotificationCleanup(string $patientId, string $title): void
    {
        $this->notificationCleanupRules[] = [
            'patient_id' => $patientId,
            'title' => $title,
            'since' => Carbon::now()->subMinute()->toIso8601String(),
        ];
    }

    protected function checkboxSelector(string $appointmentId): string
    {
        return 'input[name="appointments[]"][value="'.$appointmentId.'"]';
    }

    protected function elementCount(Browser $browser, string $selector): int
    {
        return (int) ($browser->script('return document.querySelectorAll('.json_encode($selector).').length;')[0] ?? 0);
    }

    protected function clickElement(Browser $browser, string $selector): void
    {
        $browser->script('document.querySelector('.json_encode($selector).')?.click();');
    }

    protected function setElementValue(Browser $browser, string $selector, string $value): void
    {
        $selectorJson = $this->jsonEncodeForScript($selector);
        $valueJson = $this->jsonEncodeForScript($value);

        $browser->script(<<<JS
            const el = document.querySelector({$selectorJson});
            if (el) {
                el.value = {$valueJson};
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        JS);
    }

    protected function waitForElement(Browser $browser, string $selector, int $seconds = 10): void
    {
        $browser->waitUsing($seconds, 100, fn (): bool => $this->elementCount($browser, $selector) > 0, "Element {$selector} tidak ditemukan.");
    }

    /** @return array<int, string> */
    protected function panelNotificationTitles(Browser $browser): array
    {
        return $browser->script(<<<'JS'
            return Array.from(document.querySelectorAll('#notificationPanel h3'))
                .map((el) => el.textContent.trim())
                .filter(Boolean);
        JS)[0] ?? [];
    }

    /** @param array<string, mixed> $payload */
    protected function browserPatchJson(Browser $browser, string $url, array $payload): array
    {
        return $browser->driver->executeAsyncScript(<<<'JS'
            const callback = arguments[arguments.length - 1];
            const url = arguments[0];
            const payload = arguments[1];
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(payload),
            }).then(async (response) => {
                callback({ ok: response.ok, status: response.status, body: await response.text() });
            }).catch((error) => {
                callback({ ok: false, status: 0, body: String(error) });
            });
        JS, [$url, $payload]);
    }

    protected function firestore(): FirestoreService
    {
        return app(FirestoreService::class);
    }

    /** @return array{id: string} */
    private function bestAvailableDoctor(): array
    {
        $envDoctorId = trim((string) env('DUSK_DOCTOR_ID', ''));
        if ($envDoctorId !== '') {
            return ['id' => $envDoctorId];
        }

        if ($this->doctorEmail() === 'dokter.dummy@medihub.test') {
            return ['id' => 'dokter-dummy-profile'];
        }

        $firstDoctor = $this->firestore()->all('Dokter', 1)[0] ?? null;
        if ($firstDoctor && ! blank($firstDoctor['id'] ?? '')) {
            return ['id' => (string) $firstDoctor['id']];
        }

        $doctor = $this->doctorRecordForUser($this->doctorIdentity()['user_id']);
        if ($doctor && ! blank($doctor['id'] ?? '')) {
            return ['id' => (string) $doctor['id']];
        }

        return ['id' => $this->doctorIdentity()['id']];
    }

    private function rememberDocument(string $collection, string $id): void
    {
        $this->createdDocuments[] = compact('collection', 'id');
    }

    private function cleanupFirestoreDocuments(): void
    {
        try {
            foreach ($this->appointmentsForNoteCleanup as $appointmentId) {
                foreach ($this->firestore()->where('CatatanMedis', 'appointment_id', '=', $appointmentId) as $note) {
                    $this->firestore()->delete('CatatanMedis', (string) $note['id']);
                }
            }

            foreach ($this->notificationCleanupRules as $rule) {
                foreach ($this->firestore()->where('Notifications', 'patient_id', '=', $rule['patient_id']) as $notification) {
                    $createdAt = (string) ($notification['created_at'] ?? '');
                    if (($notification['title'] ?? '') === $rule['title'] && $createdAt >= $rule['since']) {
                        $this->firestore()->delete('Notifications', (string) $notification['id']);
                    }
                }
            }

            foreach (array_reverse($this->createdDocuments) as $doc) {
                $this->firestore()->delete($doc['collection'], $doc['id']);
            }
        } catch (\Throwable) {
            // Cleanup should not mask the real Dusk failure.
        }
    }

    private function jsonEncodeForScript(string $value): string
    {
        return (string) json_encode($value, JSON_THROW_ON_ERROR);
    }
}
