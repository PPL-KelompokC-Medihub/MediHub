<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_14_003_PembatalanBeberapaJadwalTest extends Pbi14To18DuskTestCase
{
    public function test_tc_14_003_pasien_membatalkan_beberapa_jadwal_sekaligus(): void
    {
        $patient = $this->patientIdentity();
        $firstAppointment = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(5)->toDateString(),
            'appointment_time_start' => '11:00',
            'appointment_time_end' => '11:30',
            'appointment_time' => '11:00',
            'queue_number' => 92,
            'complaint' => 'TC.14.003 first appointment cancellation',
        ]);
        $secondAppointment = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(6)->toDateString(),
            'appointment_time_start' => '12:00',
            'appointment_time_end' => '12:30',
            'appointment_time' => '12:00',
            'queue_number' => 93,
            'complaint' => 'TC.14.003 second appointment cancellation',
        ]);
        $this->recordNotificationCleanup($patient['id'], 'Janji Temu Dibatalkan');

        $this->browse(function (Browser $browser) use ($firstAppointment, $secondAppointment): void {
            $firstSelector = $this->checkboxSelector((string) $firstAppointment['id']);
            $secondSelector = $this->checkboxSelector((string) $secondAppointment['id']);

            $this->loginAsPatient($browser);
            $browser->assertSee('92')
                ->assertSee('93');

            $this->waitForElement($browser, $firstSelector);
            $this->waitForElement($browser, $secondSelector);
            $this->clickElement($browser, '#toggleCancelMode');
            $this->clickElement($browser, $firstSelector);
            $this->clickElement($browser, $secondSelector);
            $this->clickElement($browser, '#submitCancelButton');

            $browser->waitForLocation('/pasien/beranda', 15)
                ->assertPathIs('/pasien/beranda');

            $this->assertSame(0, $this->elementCount($browser, $firstSelector));
            $this->assertSame(0, $this->elementCount($browser, $secondSelector));
        });

        $this->assertSame('Dibatalkan', $this->firestore()->find('BuatJadwalTemu', (string) $firstAppointment['id'])['status'] ?? null);
        $this->assertSame('Dibatalkan', $this->firestore()->find('BuatJadwalTemu', (string) $secondAppointment['id'])['status'] ?? null);
    }
}
