<?php

namespace Tests\Browser\PBI_26\ProfilePasien;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfilepasienTest extends DuskTestCase
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

    Public function test_buka_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPasien($browser);

            $browser->clickLink('Profil')
                ->waitforLocation('/pasien/profile', 10)
                ->assertPathIs('/pasien/profile')
                ->assertSee('Informasi Pribadi');
        });
    }

    public function test_update_umur_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $this->test_buka_profile($browser);

            $browser
                ->click('#editProfileBtn')
                ->pause(1000)

                ->clear('input[name="umur"]')
                ->type('input[name="umur"]', '20')

                ->clickAtXPath("//*[normalize-space()='Simpan']")
                ->pause(2000)


                ->assertPathIs('/pasien/profile')

                ->assertInputValue('input[name="umur"]', '20')
                ->screenshot('Profile-umur-berhasil-diupdate');
        });
    }

    public function test_update_profile_gagal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->test_buka_profile($browser);

            $browser
                // klik tombol Edit
                ->click('#editProfileBtn')
                ->pause(1000)

                // NEGATIF: umur sengaja dikosongkan
                ->clear('input[name="umur"]')

                // klik tombol Simpan
                ->clickAtXPath("//*[normalize-space()='Simpan']")
                ->pause(2000)

                // pastikan tetap di halaman profile
                ->assertPathIs('/pasien/profile')

                // screenshot bukti gagal
                ->screenshot('Profile-gagal-update-umur-kosong');
        });
    }
}
