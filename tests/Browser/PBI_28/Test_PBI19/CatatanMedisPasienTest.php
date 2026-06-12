<?php

namespace Tests\Browser\PBI_28\Test_PBI19;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CatatanMedisPasienTest extends DuskTestCase
{
    private function loginPasien(Browser $browser): void
    {
        $browser->visit('http://127.0.0.1:8000/login-pasien') 
                ->pause(3000)
                ->type('@login-email', 'myfesaaarofida@gmail.com')
                ->type('@login-password', 'Echafeeda*27')
                ->click('@login-button')
                ->pause(5000) // Tunggu proses Firebase/Auth selesai
                ->assertPathIs('/pasien/beranda'); 
    }

    public function testLihatDaftarDanDetailCatatanMedis(): void
{
    $this->browse(function (Browser $browser) {
        $this->loginPasien($browser);

        $browser->visit('/pasien/riwayat')
                ->waitForText('Riwayat Jadwal Temu', 10);

        // Tambahkan logic: kalau grid tidak ada, berarti datanya memang kosong
        if ($browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('[data-history-grid]'))) {
            // Jika ada data, jalankan tes seperti biasa
            $browser->assertVisible('[data-history-grid]')
                    ->click('.appointment-card')
                    ->waitFor('@history-detail-panel', 5)
                    ->assertVisible('@history-detail-panel');
        } else {
            // Jika tidak ada data, cek apakah muncul teks "Belum ada riwayat"
            $browser->assertSee('Belum ada riwayat jadwal temu');
        }
    });
}
}