<?php

namespace Tests\Unit\Pasien;

use App\Http\Requests\Pasien\StoreBookingRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * PBI-11 — Validasi form booking (StoreBookingRequest).
 *
 * Menguji aturan validasi secara langsung lewat Validator, tanpa
 * melalui HTTP, agar fokus ke aturan field saja.
 */
class StoreBookingRequestTest extends TestCase
{
    /**
     * @param array<string, mixed> $data
     */
    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new StoreBookingRequest())->rules());
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'doctor_id' => 'dok-a',
            'doctor_schedule_id' => 'sch-1',
            'appointment_time' => '08:30',
            'patient_name' => 'Budi Santoso',
            'complaint' => 'Demam tinggi.',
        ], $overrides);
    }

    public function test_minimal_valid_payload_passes(): void
    {
        $this->assertTrue($this->validate($this->validPayload())->passes());
    }

    public function test_required_fields_must_be_present(): void
    {
        $validator = $this->validate([]);

        $this->assertTrue($validator->fails());
        foreach (['doctor_id', 'doctor_schedule_id', 'appointment_time', 'patient_name', 'complaint'] as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->toArray());
        }
    }

    public function test_appointment_time_must_match_h_i_format(): void
    {
        $this->assertTrue($this->validate($this->validPayload(['appointment_time' => '8 pagi']))->fails());
        $this->assertTrue($this->validate($this->validPayload(['appointment_time' => '08:30']))->passes());
    }

    public function test_patient_email_must_be_valid_when_present(): void
    {
        $this->assertTrue($this->validate($this->validPayload(['patient_email' => 'bukan-email']))->fails());
        $this->assertTrue($this->validate($this->validPayload(['patient_email' => 'budi@example.com']))->passes());
    }

    public function test_patient_age_must_be_within_range(): void
    {
        $this->assertTrue($this->validate($this->validPayload(['patient_age' => -1]))->fails());
        $this->assertTrue($this->validate($this->validPayload(['patient_age' => 131]))->fails());
        $this->assertTrue($this->validate($this->validPayload(['patient_age' => 30]))->passes());
    }

    public function test_weight_and_height_have_numeric_bounds(): void
    {
        $this->assertTrue($this->validate($this->validPayload(['patient_weight' => 600]))->fails());
        $this->assertTrue($this->validate($this->validPayload(['patient_height' => 400]))->fails());
        $this->assertTrue($this->validate($this->validPayload(['patient_weight' => 70, 'patient_height' => 175]))->passes());
    }

    public function test_complaint_max_length_is_enforced(): void
    {
        $this->assertTrue($this->validate($this->validPayload(['complaint' => str_repeat('a', 2001)]))->fails());
    }

    public function test_authorize_requires_authenticated_user(): void
    {
        $request = new StoreBookingRequest();
        $this->assertFalse($request->authorize());
    }
}
