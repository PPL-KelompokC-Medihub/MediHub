<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_18_001_DokterMembuatCatatanMedisResepTest extends Pbi14To18DuskTestCase
{
    public function test_tc_18_001_dokter_membuat_catatan_medis_dan_resep(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->toDateString(),
            'queue_number' => 51,
            'status' => 'Diperiksa',
            'complaint' => 'TC.18.001 keluhan awal pasien',
        ]);
        $this->recordNotificationCleanup($patient['id'], 'Catatan Medis Tersedia');

        $keluhan = 'TC.18.001 keluhan utama tersimpan';
        $observasi = 'TC.18.001 hasil observasi tersimpan';
        $asesmen = 'TC.18.001 hasil asesmen tersimpan';
        $kesimpulan = 'TC.18.001 kesimpulan tersimpan';
        $rekomendasi = 'TC.18.001 rekomendasi tersimpan';
        $resep = 'TC.18.001 resep obat tersimpan';

        $this->browse(function (Browser $browser) use ($appointment, $keluhan, $observasi, $asesmen, $kesimpulan, $rekomendasi, $resep): void {
            $this->loginAsDoctor($browser);
            $browser->visit($this->appUrl('/dokter/catatan-medis/'.$appointment['id'].'/create'))
                ->waitForText('Buat Catatan Medis', 10);

            $this->setElementValue($browser, '#keluhan_utama', $keluhan);
            $this->setElementValue($browser, '#hasil_observasi', $observasi);
            $this->setElementValue($browser, '#hasil_asesmen', $asesmen);
            $this->setElementValue($browser, '#kesimpulan', $kesimpulan);
            $this->setElementValue($browser, '#rekomendasi', $rekomendasi);
            $this->setElementValue($browser, '#resep_obat', $resep);
            $this->clickElement($browser, '.cm-submit-btn');

            $browser->waitForLocation('/dokter/catatan-medis/'.$appointment['id'], 15)
                ->assertSee('Catatan medis & resep obat berhasil disimpan.')
                ->assertSee($keluhan)
                ->assertSee($resep);
        });
    }
}
