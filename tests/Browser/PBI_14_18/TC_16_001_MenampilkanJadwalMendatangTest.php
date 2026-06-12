<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_16_001_MenampilkanJadwalMendatangTest extends Pbi14To18DuskTestCase
{
    public function test_tc_16_001_pasien_melihat_daftar_jadwal_temu_mendatang(): void
    {
        $patient = $this->patientIdentity();
        $appointment = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(3)->toDateString(),
            'appointment_time_start' => '13:00',
            'appointment_time_end' => '13:30',
            'appointment_time' => '13:00',
            'queue_number' => 81,
            'status' => 'Menunggu',
        ]);

        $this->browse(function (Browser $browser) use ($appointment): void {
            $this->loginAsPatient($browser);

            $browser->assertSee('Jadwal Temu Mendatang')
                ->assertSee('RS Medic Center - Bandung')
                ->assertSee('81')
                ->assertSee('13:00 - 13:30');

            $this->waitForElement($browser, $this->checkboxSelector((string) $appointment['id']));
        });
    }
}
