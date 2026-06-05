<?php

namespace Tests\Unit;

use App\Models\Prescription;
use App\Models\FirestoreUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_creation_with_string_ids(): void
    {
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

        $response = $this->post(route('dokter.prescriptions.store'), [
            'medical_note_id' => 42,
            'patient_id' => 'patient-abc-123',
            'medications' => "Paracetamol 500mg\nAmoxicillin 500mg",
            'instructions' => 'Diminum 3x sehari setelah makan.',
        ]);

        $prescription = Prescription::first();
        $this->assertNotNull($prescription);
        $this->assertEquals(42, $prescription->medical_note_id);
        $this->assertSame('patient-abc-123', $prescription->patient_id);
        $this->assertSame('doctor-user-123', $prescription->doctor_id);
        $this->assertSame("Paracetamol 500mg\nAmoxicillin 500mg", $prescription->medications);
        $this->assertSame('Diminum 3x sehari setelah makan.', $prescription->instructions);

        $response->assertRedirect(route('dokter.dashboard'));
    }
}
