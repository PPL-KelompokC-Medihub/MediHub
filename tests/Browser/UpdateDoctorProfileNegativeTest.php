<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateDoctorProfileNegativeTest extends DuskTestCase
{
    public function test_update_profile_dokter_gagal_jika_nama_kosong(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-dokter')
                ->pause(3000)
                ->assertSee('Selamat Datang Kembali, Dokter!')

                ->type('@login-email', 'revafedayeen@gmail.com')
                ->type('@login-password', 'fedayeen123')
                ->click('@login-button')

                ->pause(8000)
                ->screenshot('negative-01-setelah-login')

                ->visit('http://127.0.0.1:8000/dokter/profil')
                ->pause(3000)
                ->screenshot('negative-02-halaman-profil');

            if ($browser->element('.profil-edit-btn')) {
                $browser->click('.profil-edit-btn')
                    ->pause(3000);
            } else {
                $browser->visit('http://127.0.0.1:8000/dokter/profile/personal')
                    ->pause(3000);
            }

            $browser->screenshot('negative-03-halaman-edit-profile')
                ->assertPathIs('/dokter/profile/personal')
                ->assertSee('Mohon Isi Informasi Data Diri Dokter dengan Lengkap')

                // Negative case: nama dokter dikosongkan
                ->clear('@doctor-name')

                // Field lain tetap diisi valid agar yang gagal benar-benar karena nama kosong
                ->type('@doctor-age', '25')
                ->type('@doctor-phone', '6287789243999')
                ->type('@doctor-weight', '50')
                ->type('@doctor-height', '155')
                ->click('@doctor-gender-male')
                ->type('@doctor-country', 'Indonesia')
                ->type('@doctor-city', 'Bandung')
                ->type('@doctor-postal-code', '40111')

                ->click('@doctor-save-profile')
                ->pause(3000)
                ->screenshot('negative-04-setelah-submit-nama-kosong')

                // Ekspektasi: tetap di halaman data diri, tidak lanjut ke keahlian
                ->assertPathIs('/dokter/profile/personal');
        });
    }
}