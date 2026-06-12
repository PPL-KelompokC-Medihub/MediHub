<?php

namespace Tests\Browser\PBI_28\Test_PBI21;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SearchDataPasienTest extends DuskTestCase
{
    private function loginDokter(Browser $browser): void
    {
        $browser->visit('http://127.0.0.1:8000/login-dokter')
                ->pause(2000)
                ->type('input[type="email"]', 'mellafesarofida@gmail.com')
                ->type('input[type="password"]', 'Echafeeda*27')
                ->click('button[type="submit"]')
                ->pause(3000)
                ->waitForLocation('/dokter/dashboard', 10);
    }

    public function test_search_pasien(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDokter($browser);

            $browser->visit('/dokter/search-pasien')
                ->waitForText('Daftar Pasien', 10);

            // 1. Search nama pasien
            $browser->type('input[name="search"]', 'Naufal')
                ->click('button#search-button')
                ->pause(1000)
                ->assertSee('Melz');

            // 2. Search dengan kolom kosong (Negative Test)
            $browser->clear('input[name="search"]')
                ->click('button#search-button')
                ->pause(500)
                ->assertSee('Masukkan nama pasien terlebih dahulu');
        });
    }
}