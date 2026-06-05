<?php

namespace Tests\Unit;

use App\Models\FirestoreUser;
use App\Services\FirestoreService;
use App\Services\Pasien\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class PasienDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_history_page_data_groups_completed_appointments_with_medical_result(): void
    {
        Carbon::setTestNow('2026-06-01 10:00:00');

        $service = $this->serviceWithAppointments([
            [
                'id' => 'history-1',
                'patient_id' => 'patient-1',
                'patient_email' => 'nadia@example.test',
                'doctor_id' => 'doctor-1',
                'appointment_date' => '2026-05-20',
                'appointment_time_start' => '09:00',
                'appointment_time_end' => '09:30',
                'status' => 'Selesai',
                'complaint' => 'Demam dan batuk',
                'diagnosa' => 'Infeksi saluran pernapasan ringan',
                'catatan_medis' => 'Istirahat cukup dan hidrasi.',
                'resep_obat' => 'Paracetamol',
            ],
            [
                'id' => 'upcoming-1',
                'patient_id' => 'patient-1',
                'patient_email' => 'nadia@example.test',
                'doctor_id' => 'doctor-1',
                'appointment_date' => '2026-06-10',
                'appointment_time_start' => '14:00',
                'appointment_time_end' => '14:30',
                'status' => 'Menunggu',
            ],
            [
                'id' => 'other-patient',
                'patient_id' => 'patient-2',
                'patient_email' => 'other@example.test',
                'doctor_id' => 'doctor-1',
                'appointment_date' => '2026-05-20',
                'status' => 'Selesai',
            ],
        ]);

        $data = $service->historyPageData();

        $this->assertCount(1, $data['riwayatJadwal']);
        $this->assertSame('history-1', $data['riwayatJadwal'][0]['id']);
        $this->assertSame('Selesai', $data['riwayatJadwal'][0]['status']);
        $this->assertSame('Infeksi saluran pernapasan ringan', $data['riwayatJadwal'][0]['diagnosa']);
        $this->assertSame('Paracetamol', $data['riwayatJadwal'][0]['resep_obat']);

        $this->assertCount(1, $data['jadwalMendatang']);
        $this->assertSame('upcoming-1', $data['jadwalMendatang'][0]['id']);
    }

    public function test_history_page_data_moves_past_pending_appointments_to_history(): void
    {
        Carbon::setTestNow('2026-06-01 10:00:00');

        $service = $this->serviceWithAppointments([
            [
                'id' => 'past-pending',
                'patient_id' => 'patient-1',
                'patient_email' => 'nadia@example.test',
                'doctor_id' => 'doctor-1',
                'appointment_date' => '2026-05-31',
                'appointment_time_start' => '08:00',
                'status' => 'Menunggu',
            ],
        ]);

        $data = $service->historyPageData();

        $this->assertCount(1, $data['riwayatJadwal']);
        $this->assertSame('past-pending', $data['riwayatJadwal'][0]['id']);
        $this->assertSame('pending', $data['riwayatJadwal'][0]['status_key']);
        $this->assertCount(0, $data['jadwalMendatang']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $appointments
     */
    private function serviceWithAppointments(array $appointments): DashboardService
    {
        Auth::setUser(new FirestoreUser([
            'id' => 'patient-1',
            'email' => 'nadia@example.test',
            'name' => 'Nadia',
            'role' => 'pasien',
        ]));

        $firestore = Mockery::mock(FirestoreService::class);
        $firestore->shouldReceive('all')->with('Pasien')->andReturn([
            [
                'id' => 'patient-profile-1',
                'user_id' => 'patient-1',
                'fullname' => 'Nadia',
                'profile_pict' => null,
            ],
        ]);
        $firestore->shouldReceive('all')->with('Users')->andReturn([
            [
                'id' => 'doctor-user-1',
                'role' => 'dokter',
                'fullname' => 'Bima',
            ],
        ]);
        $firestore->shouldReceive('all')->with('Dokter_spesialisasi')->andReturn([
            [
                'id' => 'specialization-1',
                'dokterid' => 'doctor-1',
                'service' => 'Poli Umum',
            ],
        ]);
        $firestore->shouldReceive('all')->with('Dokter')->andReturn([
            [
                'id' => 'doctor-1',
                'usersId' => 'doctor-user-1',
                'email' => 'bima@example.test',
            ],
        ]);
        $firestore->shouldReceive('all')->with('BuatJadwalTemu')->andReturn($appointments);

        return new DashboardService($firestore, new \App\Services\MedihubFirestoreRepository($firestore));
    }

    public function test_history_page_data_merges_mysql_medical_notes_and_prescriptions(): void
    {
        Carbon::setTestNow('2026-06-01 10:00:00');

        $service = $this->serviceWithAppointments([
            [
                'id' => 'history-db-match',
                'patient_id' => 'patient-1',
                'patient_email' => 'nadia@example.test',
                'doctor_id' => 'doctor-1',
                'appointment_date' => '2026-05-20',
                'status' => 'Selesai',
            ],
        ]);

        $note = \App\Models\MedicalNote::create([
            'patient_id' => 'patient-1',
            'doctor_id' => 'doctor-user-1',
            'notes' => 'Didiagnosis influenza dari database.',
        ]);
        $note->created_at = Carbon::parse('2026-05-20 14:00:00');
        $note->save();

        \App\Models\Prescription::create([
            'medical_note_id' => $note->id,
            'patient_id' => 'patient-1',
            'doctor_id' => 'doctor-user-1',
            'medications' => 'Amoxillin 500mg',
        ]);

        $data = $service->historyPageData();

        $this->assertCount(1, $data['riwayatJadwal']);
        $this->assertSame('history-db-match', $data['riwayatJadwal'][0]['id']);
        $this->assertSame('Didiagnosis influenza dari database.', $data['riwayatJadwal'][0]['diagnosa']);
        $this->assertSame('Didiagnosis influenza dari database.', $data['riwayatJadwal'][0]['catatan_medis']);
        $this->assertSame('Amoxillin 500mg', $data['riwayatJadwal'][0]['resep_obat']);
    }
}
