<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_16_003_LabelHariRealtimeTest extends Pbi14To18DuskTestCase
{
    public function test_tc_16_003_label_hari_real_time_hari_ini_dan_besok(): void
    {
        $patient = $this->patientIdentity();
        $today = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->toDateString(),
            'appointment_time_start' => '14:00',
            'appointment_time_end' => '14:30',
            'appointment_time' => '14:00',
            'queue_number' => 85,
            'status' => 'Menunggu',
        ]);
        $tomorrow = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDay()->toDateString(),
            'appointment_time_start' => '15:00',
            'appointment_time_end' => '15:30',
            'appointment_time' => '15:00',
            'queue_number' => 86,
            'status' => 'Menunggu',
        ]);

        $this->browse(function (Browser $browser) use ($today, $tomorrow): void {
            $this->loginAsPatient($browser);

            $this->waitForElement($browser, $this->checkboxSelector((string) $today['id']));
            $this->waitForElement($browser, $this->checkboxSelector((string) $tomorrow['id']));

            $browser->assertSee('Hari ini')
                ->assertSee('Besok')
                ->assertSee('85')
                ->assertSee('86');
        });
    }
}
