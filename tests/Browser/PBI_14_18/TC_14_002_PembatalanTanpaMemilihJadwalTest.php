<?php

namespace Tests\Browser\PBI_14_18;

use Laravel\Dusk\Browser;

class TC_14_002_PembatalanTanpaMemilihJadwalTest extends Pbi14To18DuskTestCase
{
    public function test_tc_14_002_pasien_tidak_bisa_membatalkan_tanpa_memilih_jadwal(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginAsPatient($browser);

            $browser->assertSee('Jadwal Temu Mendatang');
            $this->clickElement($browser, '#toggleCancelMode');
            $this->clickElement($browser, '#submitCancelButton');

            $browser->waitForLocation('/pasien/beranda', 15)
                ->assertPathIs('/pasien/beranda')
                ->assertSee('Pilih jadwal temu yang ingin dibatalkan terlebih dahulu.');
        });
    }
}
