<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_14_001_PembatalanBookingBerhasilTest extends Pbi14To18DuskTestCase
{
    public function test_tc_14_001_pasien_membatalkan_satu_jadwal_temu_yang_dipilih(): void
    {
        $patient = $this->patientIdentity();
        $appointment = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(4)->toDateString(),
            'queue_number' => 91,
            'complaint' => 'TC.14.001 appointment cancellation',
        ]);
        $this->recordNotificationCleanup($patient['id'], 'Janji Temu Dibatalkan');

        $this->browse(function (Browser $browser) use ($appointment): void {
            $selector = $this->checkboxSelector((string) $appointment['id']);

            $this->loginAsPatient($browser);
            $browser->assertSee('Jadwal Temu Mendatang')
                ->assertSee('91');

            $this->waitForElement($browser, $selector);
            $this->clickElement($browser, '#toggleCancelMode');
            $this->clickElement($browser, $selector);
            $this->clickElement($browser, '#submitCancelButton');

            $browser->waitForLocation('/pasien/beranda', 15)
                ->assertPathIs('/pasien/beranda');

            $this->assertSame(0, $this->elementCount($browser, $selector));
        });

        $updated = $this->firestore()->find('BuatJadwalTemu', (string) $appointment['id']);
        $this->assertSame('Dibatalkan', $updated['status'] ?? null);
    }
}
