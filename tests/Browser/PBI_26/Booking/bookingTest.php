<?php

namespace Tests\Browser\PBI_26\Booking;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class bookingTest extends DuskTestCase
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

    public function test_booking(): void
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

                ->clickAtXPath("//*[normalize-space()='14']")

                ->clickAtXPath("//*[normalize-space()='06:00']");

            $browser->script("
                const infoPasien = [...document.querySelectorAll('*')]
                    .find(el => el.textContent.trim() === 'Informasi Pasien');

                if (infoPasien) {
                    infoPasien.scrollIntoView({
                        behavior: 'instant',
                        block: 'start'
                    });
                }
            ");

            $browser->pause(1000)

                ->type('textarea[name="allergy_history"]', 'Tidak ada')

                ->type('textarea[name="complaint"]', 'Demam dan pusing sejak 2 hari')

                ->clickAtXPath("//*[contains(normalize-space(), 'Buat Jadwal Temu')]")

                ->pause(3000)
                ->screenshot('booking-jadwal-temu-berhasil');
        });
    }

}
