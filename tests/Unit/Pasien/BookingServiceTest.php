<?php

namespace Tests\Unit\Pasien;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use App\Services\Pasien\BookingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * PBI-11 — Pilih Jadwal & Booking Temu Dokter (+ Autofill Data Pasien).
 *
 * Logika inti booking ada di BookingService. FirestoreService di-mock
 * agar tidak ada panggilan HTTP nyata ke Firestore saat test berjalan.
 */
class BookingServiceTest extends TestCase
{
    private FirestoreService $firestore;

    private MedihubFirestoreRepository $repository;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firestore = Mockery::mock(FirestoreService::class);
        $this->repository = Mockery::mock(MedihubFirestoreRepository::class);
        // createNotification dipanggil di alur booking sukses (PBI-15);
        // default-kan agar tidak mengganggu assertion test lain.
        $this->repository->shouldReceive('createNotification')->andReturnNull()->byDefault();
        $this->service = new BookingService($this->firestore, $this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function actingAsPatient(string $uid = 'patient-1'): void
    {
        Auth::setUser(new FirestoreUser([
            'id' => $uid,
            'fullname' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'pasien',
        ]));
    }

    public function test_form_data_autofills_patient_profile_from_firestore(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Pasien', 'patient-1')
            ->andReturn([
                'fullname' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'umur' => 30,
                'gender' => 'Laki-laki',
                'weight' => 70,
                'height' => 175,
                'blood_type' => 'O',
                'allergy_history' => 'Tidak ada',
            ]);

        // Tidak ada dokter/jadwal untuk skenario ini.
        $this->firestore->shouldReceive('all')->andReturn([]);

        $data = $this->service->formData();

        $this->assertSame('Budi Santoso', $data['patient']['fullname']);
        $this->assertSame('budi@example.com', $data['patient']['email']);
        $this->assertSame(30, $data['patient']['umur']);
        $this->assertSame('Laki-laki', $data['patient']['gender']);
        $this->assertSame('O', $data['patient']['blood_type']);
    }

    public function test_form_data_falls_back_to_auth_user_when_patient_doc_missing(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Pasien', 'patient-1')
            ->andReturn(null);

        $this->firestore->shouldReceive('all')->andReturn([]);

        $data = $this->service->formData();

        $this->assertSame('Budi Santoso', $data['patient']['fullname']);
        $this->assertSame('budi@example.com', $data['patient']['email']);
        $this->assertNull($data['patient']['umur']);
    }

    public function test_form_data_selects_first_doctor_with_schedule_when_none_requested(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')->with('Pasien', 'patient-1')->andReturn([]);

        $this->firestore->shouldReceive('all')->with('Users')->andReturn([
            ['id' => 'user-a', 'fullname' => 'Dr. Andi'],
            ['id' => 'user-b', 'fullname' => 'Dr. Bella'],
        ]);
        $this->firestore->shouldReceive('all')->with('Dokter_spesialisasi')->andReturn([]);
        $this->firestore->shouldReceive('all')->with('Dokter')->andReturn([
            ['id' => 'dok-a', 'usersId' => 'user-a', 'email' => 'andi@example.com'],
            ['id' => 'dok-b', 'usersId' => 'user-b', 'email' => 'bella@example.com'],
        ]);
        $this->firestore->shouldReceive('all')->with('JadwalDokter')->andReturn([
            ['id' => 'sch-1', 'dokterid' => 'user-b', 'tanggal' => '2026-06-02', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00'],
        ]);
        $this->firestore->shouldReceive('all')->with('BuatJadwalTemu')->andReturn([]);

        $data = $this->service->formData();

        // Jadwal pertama milik user-b → dipetakan ke dok-b.
        $this->assertSame('dok-b', $data['selectedDoctorId']);
        $this->assertCount(2, $data['doctors']);
        $this->assertCount(1, $data['schedules']);
    }

    public function test_form_data_honors_valid_requested_doctor_id(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')->with('Pasien', 'patient-1')->andReturn([]);
        $this->firestore->shouldReceive('all')->with('Users')->andReturn([]);
        $this->firestore->shouldReceive('all')->with('Dokter_spesialisasi')->andReturn([]);
        $this->firestore->shouldReceive('all')->with('Dokter')->andReturn([
            ['id' => 'dok-a', 'usersId' => 'user-a', 'email' => 'andi@example.com'],
            ['id' => 'dok-b', 'usersId' => 'user-b', 'email' => 'bella@example.com'],
        ]);
        $this->firestore->shouldReceive('all')->with('JadwalDokter')->andReturn([]);
        $this->firestore->shouldReceive('all')->with('BuatJadwalTemu')->andReturn([]);

        $data = $this->service->formData('dok-a');

        $this->assertSame('dok-a', $data['selectedDoctorId']);
    }

    public function test_create_appointment_rejects_unknown_doctor(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'ghost')
            ->andReturn(null);

        $this->expectException(ValidationException::class);

        try {
            $this->service->createAppointment($this->validBookingData(['doctor_id' => 'ghost']), null);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('doctor_id', $e->errors());
            throw $e;
        }
    }

    public function test_create_appointment_rejects_schedule_not_belonging_to_doctor(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn(['id' => 'sch-1', 'dokterid' => 'user-z', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);

        $this->expectException(ValidationException::class);

        try {
            $this->service->createAppointment($this->validBookingData(), null);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('doctor_schedule_id', $e->errors());
            throw $e;
        }
    }

    public function test_create_appointment_rejects_time_outside_schedule(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn(['id' => 'sch-1', 'dokterid' => 'user-a', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);

        $this->expectException(ValidationException::class);

        try {
            // 11:00 berada di luar 08:00-10:00.
            $this->service->createAppointment($this->validBookingData(['appointment_time' => '11:00']), null);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('appointment_time', $e->errors());
            throw $e;
        }
    }

    public function test_create_appointment_rejects_non_30_minute_aligned_time(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn(['id' => 'sch-1', 'dokterid' => 'user-a', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);

        $this->expectException(ValidationException::class);

        // 08:20 tidak kelipatan 30 menit dari jam mulai.
        $this->service->createAppointment($this->validBookingData(['appointment_time' => '08:20']), null);
    }

    public function test_create_appointment_rejects_time_already_booked(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn(['id' => 'sch-1', 'dokterid' => 'user-a', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);

        $this->firestore->shouldReceive('where')
            ->with('BuatJadwalTemu', 'doctor_schedule_id', '=', 'sch-1')
            ->andReturn([
                ['appointment_time' => '08:00'],
            ]);

        $this->expectException(ValidationException::class);

        try {
            $this->service->createAppointment($this->validBookingData(['appointment_time' => '08:00']), null);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('appointment_time', $e->errors());
            throw $e;
        }
    }

    public function test_create_appointment_persists_with_expected_fields_on_success(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('Users', 'user-a')
            ->andReturn(['fullname' => 'Andi']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn([
                'id' => 'sch-1',
                'dokterid' => 'user-a',
                'tanggal' => '2026-06-02',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
            ]);

        $this->firestore->shouldReceive('where')
            ->with('BuatJadwalTemu', 'doctor_schedule_id', '=', 'sch-1')
            ->andReturn([]);

        $captured = null;
        $this->firestore->shouldReceive('add')
            ->once()
            ->with('BuatJadwalTemu', Mockery::on(function (array $payload) use (&$captured): bool {
                $captured = $payload;

                return true;
            }))
            ->andReturn(['id' => 'appt-1']);

        $this->service->createAppointment($this->validBookingData(['appointment_time' => '08:30']), null);

        $this->assertSame('dok-a', $captured['doctor_id']);
        $this->assertSame('sch-1', $captured['doctor_schedule_id']);
        $this->assertSame('08:30', $captured['appointment_time']);
        $this->assertSame('08:30', $captured['appointment_time_start']);
        $this->assertSame('09:00', $captured['appointment_time_end']);
        $this->assertSame('2026-06-02', $captured['appointment_date']);
        $this->assertSame('08:00 - 10:00', $captured['schedule_time_range']);
        $this->assertSame(1, $captured['queue_number']);
        $this->assertSame('Menunggu', $captured['status']);
        $this->assertSame('patient-1', $captured['patient_id']);
        $this->assertNull($captured['medical_doc']);
    }

    public function test_create_appointment_increments_queue_number_and_stores_uploaded_doc(): void
    {
        Storage::fake('public');
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('Dokter', 'dok-a')
            ->andReturn(['id' => 'dok-a', 'usersId' => 'user-a']);

        $this->firestore->shouldReceive('find')
            ->with('Users', 'user-a')
            ->andReturn(['fullname' => 'Andi']);

        $this->firestore->shouldReceive('find')
            ->with('JadwalDokter', 'sch-1')
            ->andReturn([
                'id' => 'sch-1',
                'dokterid' => 'user-a',
                'tanggal' => '2026-06-02',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
            ]);

        // Sudah ada 2 booking di jadwal ini (jam berbeda) → antrian berikutnya 3.
        $this->firestore->shouldReceive('where')
            ->with('BuatJadwalTemu', 'doctor_schedule_id', '=', 'sch-1')
            ->andReturn([
                ['appointment_time' => '08:00'],
                ['appointment_time' => '08:30'],
            ]);

        $captured = null;
        $this->firestore->shouldReceive('add')
            ->once()
            ->with('BuatJadwalTemu', Mockery::on(function (array $payload) use (&$captured): bool {
                $captured = $payload;

                return true;
            }))
            ->andReturn(['id' => 'appt-3']);

        $doc = UploadedFile::fake()->create('rujukan.pdf', 100, 'application/pdf');

        $this->service->createAppointment($this->validBookingData(['appointment_time' => '09:00']), $doc);

        $this->assertSame(3, $captured['queue_number']);
        $this->assertNotNull($captured['medical_doc']);
        Storage::disk('public')->assertExists($captured['medical_doc']);
    }

    public function test_delete_appointment_cancels_current_patient_appointment_and_notifies(): void
    {
        $this->actingAsPatient();
        $updatedPayload = null;
        $notificationPayload = null;

        $this->firestore->shouldReceive('find')
            ->with('BuatJadwalTemu', 'appt-1')
            ->andReturn([
                'id' => 'appt-1',
                'patient_id' => 'patient-1',
                'patient_email' => 'budi@example.com',
            ]);

        $this->firestore->shouldReceive('update')
            ->once()
            ->with('BuatJadwalTemu', 'appt-1', Mockery::on(function (array $payload) use (&$updatedPayload): bool {
                $updatedPayload = $payload;

                return ($payload['status'] ?? null) === 'Dibatalkan'
                    && array_key_exists('update_at', $payload);
            }));

        $this->repository->shouldReceive('deleteAppointment')->never();
        $this->repository->shouldReceive('createNotification')
            ->once()
            ->with(Mockery::on(function (array $payload) use (&$notificationPayload): bool {
                $notificationPayload = $payload;

                return $payload['patient_id'] === 'patient-1'
                    && $payload['type'] === 'cancel';
            }));

        $this->service->deleteAppointment(['appt-1']);

        $this->assertSame('Dibatalkan', $updatedPayload['status']);
        $this->assertSame('cancel', $notificationPayload['type']);
    }

    public function test_delete_appointment_rejects_other_patient_appointment(): void
    {
        $this->actingAsPatient();

        $this->firestore->shouldReceive('find')
            ->with('BuatJadwalTemu', 'appt-2')
            ->andReturn([
                'id' => 'appt-2',
                'patient_id' => 'other-patient',
                'patient_email' => 'other@example.com',
            ]);

        $this->repository->shouldReceive('deleteAppointment')->never();

        $this->expectException(ValidationException::class);

        try {
            $this->service->deleteAppointment(['appt-2']);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('appointment_id', $e->errors());
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'doctor_id' => 'dok-a',
            'doctor_schedule_id' => 'sch-1',
            'appointment_time' => '08:30',
            'patient_name' => 'Budi Santoso',
            'patient_email' => 'budi@example.com',
            'complaint' => 'Demam tinggi sejak kemarin.',
        ], $overrides);
    }
}
