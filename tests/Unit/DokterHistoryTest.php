<?php

namespace Tests\Unit;

use App\Models\FirestoreUser;
use App\Models\MedicalNote;
use App\Models\Prescription;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class DokterHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_page_returns_historical_appointments_filtered(): void
    {
        // Authenticate doctor
        Auth::setUser(new FirestoreUser([
            'id' => 'doctor-user-123',
            'email' => 'doctor@example.test',
            'role' => 'dokter',
            'name' => 'Dr. John Doe',
            'phone' => '081234567890',
            'age' => 40,
            'weight' => 70,
            'height' => 175,
            'gender' => 'Laki-laki',
            'country' => 'Indonesia',
            'city' => 'Bandung',
            'postal_code' => '40123',
            'specialty' => 'Umum',
            'started_practice_year' => '2015',
            'education_institution' => 'Universitas Indonesia',
            'bio' => 'Dokter berpengalaman',
            'services' => ['Konsultasi Umum'],
            'certification1' => 'Sertifikat Kompetensi',
        ]));

        session(['medihub_user_role' => 'dokter']);

        // Mock Firestore appointments
        // One completed in past, one pending in future
        $appointments = [
            [
                'id' => 'appointment-past',
                'dokterid' => 'doctor-123',
                'patient_id' => 'patient-1',
                'patient_name' => 'Sari Melati',
                'appointment_date' => '2026-05-20',
                'status' => 'Selesai',
                'complaint' => 'Batuk pilek',
            ],
            [
                'id' => 'appointment-future-pending',
                'dokterid' => 'doctor-123',
                'patient_id' => 'patient-2',
                'patient_name' => 'Budi Santoso',
                'appointment_date' => '2026-06-30',
                'status' => 'Menunggu',
                'complaint' => 'Sakit kepala',
            ],
        ];

        // Create local DB entries for patient-1
        $note = MedicalNote::create([
            'doctor_id' => 'doctor-user-123',
            'patient_id' => 'patient-1',
            'notes' => 'Pasien menderita influenza ringan.',
        ]);

        Prescription::create([
            'medical_note_id' => $note->id,
            'doctor_id' => 'doctor-user-123',
            'patient_id' => 'patient-1',
            'medications' => 'Paracetamol 500mg',
        ]);

        // Mock Firestore
        $firestore = Mockery::mock(FirestoreService::class);
        $firestore->shouldReceive('where')->andReturnUsing(function (
            string $collection,
            string $field,
            string $operator,
            mixed $value,
            ?int $limit = null,
        ) use ($appointments): array {
            if ($collection === 'Dokter' && $field === 'usersId') {
                return [['id' => 'doctor-123', 'usersId' => 'doctor-user-123']];
            }
            if ($collection === 'BuatJadwalTemu') {
                return $appointments;
            }
            return [];
        });

        $this->app->instance(FirestoreService::class, $firestore);

        // Mock Repository
        $repository = Mockery::mock(MedihubFirestoreRepository::class);
        $repository->shouldReceive('findUser')->with('doctor-user-123')->andReturn([
            'id' => 'doctor-user-123',
            'fullname' => 'Dr. John Doe',
        ]);
        $repository->shouldReceive('hydrateDoctorData')->andReturn([
            'name' => 'Dr. John Doe',
            'profile_pict' => null,
        ]);
        $this->app->instance(MedihubFirestoreRepository::class, $repository);

        // Call history page
        $response = $this->get(route('dokter.riwayat'));

        $response->assertStatus(200);
        $response->assertViewIs('dokter.riwayat');

        // Check view data variables
        $viewData = $response->original->getData();
        $this->assertCount(1, $viewData['appointments']);
        $this->assertSame('Sari Melati', $viewData['appointments'][0]->patient_name);
        $this->assertSame('Selesai', $viewData['appointments'][0]->display_status);

        $this->assertCount(1, $viewData['medicalNotes']);
        $this->assertSame('Pasien menderita influenza ringan.', $viewData['medicalNotes'][0]->notes);
    }
}
