<?php

namespace Tests\Unit;

use App\Models\MedicalNote;
use App\Models\FirestoreUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MedicalNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_note_creation_with_string_ids(): void
    {
        // Set authenticated doctor
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

        // Post request to store medical note
        session(['medihub_user_role' => 'dokter']);

        // Post request to store medical note
        $response = $this->post(route('dokter.medical_notes.store'), [
            'patient_id' => 'patient-abc-123',
            'notes' => 'Pasien menderita influenza ringan. Disarankan istirahat.',
        ]);

        // Verify it redirects to prescription create page
        $note = MedicalNote::first();
        $this->assertNotNull($note);
        $this->assertSame('patient-abc-123', $note->patient_id);
        $this->assertSame('doctor-user-123', $note->doctor_id);
        $this->assertSame('Pasien menderita influenza ringan. Disarankan istirahat.', $note->notes);

        $response->assertRedirect(route('dokter.prescriptions.create', [
            'patient_id' => 'patient-abc-123',
            'medical_note_id' => $note->id,
        ]));
    }

    public function test_medical_note_creation_without_patient_id(): void
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

        $response = $this->post(route('dokter.medical_notes.store'), [
            'notes' => 'Catatan umum saja.',
        ]);

        $note = MedicalNote::first();
        $this->assertNotNull($note);
        $this->assertNull($note->patient_id);

        $response->assertRedirect(route('dokter.dashboard'));
    }
}
