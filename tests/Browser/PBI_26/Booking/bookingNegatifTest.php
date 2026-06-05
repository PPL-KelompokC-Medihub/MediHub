<?php

namespace Tests\Browser\PBI_26\Booking;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class bookingNegatifTest extends DuskTestCase
{
    private function loginPasien(Browser $browser): void
    {
        $browser->visit('http://127.0.0.1:8000/login-pasien')
            ->waitFor('@login-email', 10)
            ->clear('@login-email')
            ->type('@login-email', 'naufalnr19@gmail.com')
            ->clear('@login-password')
            ->type('@login-password', '@Naufi06')
            ->click('@login-button')
            ->waitForLocation('/pasien/beranda', 30)
            ->assertPathIs('/pasien/beranda');
    }

    public function test_booking_gagal(): void
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
                ->waitForLocation('/pasien/booking', 20)
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
                ->type('textarea[name=\"allergy_history\"]', 'Tidak ada')

                ->clear('textarea[name=\"complaint\"]')

                ->clickAtXPath("//*[contains(normalize-space(), 'Buat Jadwal Temu')]")
                ->pause(2000)

                ->assertPathIs('/pasien/booking')

                ->screenshot('booking-gagal-keluhan-kosong');
        });
    }
}