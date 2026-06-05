<?php

namespace Tests\Browser\PBI_26\PBI10;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DaftarjadwalTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    private function loginPasien(Browser $browser): void
    {
            $browser->visit('http://127.0.0.1:8000/login-pasien')
                ->pause(3000)
                ->type('@login-email', 'naufalnr19@gmail.com')
                ->type('@login-password', '@Naufi06')
                ->click('@login-button')
                ->pause(3000)
                ->waitForLocation('/pasien/beranda', 10)
                ->assertPathIs('/pasien/beranda');
    }

    public function test_buka_daftar_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPasien($browser);

            $browser->assertPathIs('/pasien/beranda')
                ->waitForText('Dokter Pilihan Pasien', 10);

            $browser->script("
                const dokterRafi = [...document.querySelectorAll('*')]
                    .find(el => el.textContent.trim() === 'dr. Rafi');

                if (dokterRafi) {
                    dokterRafi.scrollIntoView({
                        behavior: 'instant',
                        block: 'center',
                        inline: 'center'
                    });
                }
            ");

            $browser->pause(1000)
                ->clickAtXPath("//*[normalize-space()='dr. Rafi']")
                ->waitForLocation('/pasien/booking', 10)
                ->assertPathIs('/pasien/booking')
                ->assertQueryStringHas('doctor_id')
                ->assertSee('Jadwal Tersedia')
                ->assertSee('Waktu Tersedia')
                ->screenshot('booking-dr-rafi-berhasil');
        });
    }

}
