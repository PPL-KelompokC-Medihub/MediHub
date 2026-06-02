<?php

namespace Tests\Browser\PBI_26\Layanan;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomepageTest extends DuskTestCase
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

    Public function test_buka_Homepage(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPasien($browser);

            $browser->clickLink('Beranda')
                ->waitforLocation('/pasien/beranda', 10)
                ->screenshot('beranda-berhasil');
        });
    }
}
