<?php

namespace Tests\Browser\PBI_26\ProfileDokter;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfiledokterTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    private function loginDokter(Browser $browser): void
    {
            $browser->visit('http://127.0.0.1:8000/login-dokter')
                ->pause(3000)
                ->type('@login-email', 'hakunamataku19@gmail.com')
                ->type('@login-password', '@Naufi06')
                ->click('@login-button')
                ->pause(3000)
                ->waitForLocation('/dokter/dashboard', 10)
                ->assertPathIs('/dokter/dashboard');
    }

    public function test_buka_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDokter($browser);

            $browser->clickLink('Profil')
                ->waitforLocation('/dokter/profil', 10)
                ->assertPathIs('/dokter/profil')
                ->assertSee('Data Diri Dokter');
        });
    }

    public function test_update_data_diri_dokter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->test_buka_profile($browser);

            $browser
                ->clickLink('Edit Profil')
                ->waitForText('Data Diri Dokter', 15)

                ->waitFor('@doctor-name', 10)

                ->clear('@doctor-name')
                ->type('@doctor-name', 'Naufal Rafi Dokter')

                ->clear('@doctor-age')
                ->type('@doctor-age', '20')

                ->clear('@doctor-phone')
                ->type('@doctor-phone', '081234567890')

                ->clear('@doctor-weight')
                ->type('@doctor-weight', '70')

                ->clear('@doctor-height')
                ->type('@doctor-height', '175')

                ->click('@doctor-gender-male')

                ->clear('@doctor-country')
                ->type('@doctor-country', 'Indonesia')

                ->clear('@doctor-city')
                ->type('@doctor-city', 'Bandung')

                ->clear('@doctor-postal-code')
                ->type('@doctor-postal-code', '40111')

                ->click('@doctor-save-profile')
                ->pause(3000)
                ->screenshot('update-data-diri-dokter-berhasil');
        });
    }

    public function test_update_data_diri_dokter_gagal_jika_nama_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->test_buka_profile($browser);

            $browser
                ->clickLink('Edit Profil')
                ->waitForText('Data Diri Dokter', 15)
                ->waitFor('@doctor-name', 10)

                ->clear('@doctor-name')

                ->clear('@doctor-age')
                ->type('@doctor-age', '20')

                ->clear('@doctor-phone')
                ->type('@doctor-phone', '081234567890')

                ->clear('@doctor-weight')
                ->type('@doctor-weight', '70')

                ->clear('@doctor-height')
                ->type('@doctor-height', '175')

                ->click('@doctor-gender-male')

                ->clear('@doctor-country')
                ->type('@doctor-country', 'Indonesia')

                ->clear('@doctor-city')
                ->type('@doctor-city', 'Bandung')

                ->clear('@doctor-postal-code')
                ->type('@doctor-postal-code', '40111')

                ->click('@doctor-save-profile')
                ->pause(2000)

                ->assertSee('Data Diri Dokter')

                ->screenshot('update-data-diri-dokter-gagal-nama-kosong');
        });
    }
}