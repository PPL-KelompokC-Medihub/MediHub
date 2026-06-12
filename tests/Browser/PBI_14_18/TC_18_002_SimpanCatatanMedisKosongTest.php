<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_18_002_SimpanCatatanMedisKosongTest extends Pbi14To18DuskTestCase
{
    public function test_tc_18_002_dokter_tidak_bisa_menyimpan_catatan_medis_wajib_kosong(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->toDateString(),
            'queue_number' => 52,
            'status' => 'Diperiksa',
            'complaint' => 'TC.18.002 keluhan awal pasien',
        ]);

        $this->browse(function (Browser $browser) use ($appointment): void {
            $this->loginAsDoctor($browser);
            $browser->visit($this->appUrl('/dokter/catatan-medis/'.$appointment['id'].'/create'))
                ->waitForText('Buat Catatan Medis', 10);

            $this->setElementValue($browser, '#keluhan_utama', '');
            $this->clickElement($browser, '.cm-submit-btn');

            $validity = $browser->script(<<<'JS'
                const field = document.querySelector('#keluhan_utama');
                return { valid: field.checkValidity(), message: field.validationMessage };
            JS)[0] ?? ['valid' => true, 'message' => ''];

            $this->assertFalse((bool) $validity['valid']);
            $this->assertNotSame('', trim((string) $validity['message']));
            $browser->assertPathIs('/dokter/catatan-medis/'.$appointment['id'].'/create');
        });

        $notes = $this->firestore()->where('CatatanMedis', 'appointment_id', '=', (string) $appointment['id']);
        $this->assertCount(0, $notes);
    }
}
