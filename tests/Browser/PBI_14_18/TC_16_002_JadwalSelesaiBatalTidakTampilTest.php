<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_16_002_JadwalSelesaiBatalTidakTampilTest extends Pbi14To18DuskTestCase
{
    public function test_tc_16_002_jadwal_selesai_dan_batal_tidak_tampil_di_mendatang(): void
    {
        $patient = $this->patientIdentity();
        $active = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(3)->toDateString(),
            'queue_number' => 82,
            'status' => 'Menunggu',
        ]);
        $completed = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(4)->toDateString(),
            'queue_number' => 83,
            'status' => 'Selesai',
        ]);
        $cancelled = $this->createPatientAppointment($patient, [
            'appointment_date' => Carbon::now()->addDays(5)->toDateString(),
            'queue_number' => 84,
            'status' => 'Dibatalkan',
        ]);

        $this->browse(function (Browser $browser) use ($active, $completed, $cancelled): void {
            $this->loginAsPatient($browser);

            $activeSelector = $this->checkboxSelector((string) $active['id']);
            $completedSelector = $this->checkboxSelector((string) $completed['id']);
            $cancelledSelector = $this->checkboxSelector((string) $cancelled['id']);

            $this->waitForElement($browser, $activeSelector);
            $this->assertSame(0, $this->elementCount($browser, $completedSelector));
            $this->assertSame(0, $this->elementCount($browser, $cancelledSelector));
        });
    }
}
