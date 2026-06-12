<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_17_002_UpdateStatusTanpaMemilihStatusTest extends Pbi14To18DuskTestCase
{
    public function test_tc_17_002_update_status_tanpa_memilih_status_ditolak(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->subDays(2)->toDateString(),
            'queue_number' => 42,
            'status' => 'Menunggu',
            'complaint' => 'TC.17.002 empty status validation',
        ]);

        $this->browse(function (Browser $browser) use ($appointment): void {
            $this->loginAsDoctor($browser);
            $browser->visit($this->appUrl('/dokter/riwayat'))
                ->waitForText('Riwayat', 10);

            $response = $this->browserPatchJson(
                $browser,
                $this->appUrl('/dokter/appointment/'.$appointment['id'].'/status'),
                [],
            );

            $this->assertSame(422, $response['status'] ?? null);
            $this->assertStringContainsString('status', strtolower((string) ($response['body'] ?? '')));
        });

        $updated = $this->firestore()->find('BuatJadwalTemu', (string) $appointment['id']);
        $this->assertSame('Menunggu', $updated['status'] ?? null);
    }
}
