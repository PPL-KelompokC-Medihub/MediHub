<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateDoctorProfileTest extends DuskTestCase
{
    public function test_update_profile_dokter_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $namaBaru = 'revafedayeen automation';

            $browser->visit('http://127.0.0.1:8000/login-dokter')
                ->pause(3000)
                ->screenshot('01-halaman-login-dokter')
                ->assertSee('Selamat Datang Kembali, Dokter!')

                ->type('@login-email', 'revafedayeen@gmail.com')
                ->type('@login-password', 'fedayeen123')
                ->click('@login-button')

                ->pause(8000)
                ->screenshot('02-setelah-login-dokter')
                ->assertDontSee('Selamat Datang Kembali, Dokter!')

                // Masuk ke halaman profil dokter
                ->visit('http://127.0.0.1:8000/dokter/profil')
                ->pause(3000)
                ->screenshot('03-halaman-profil-dokter');

            // Kalau tombol Edit Profil ada, klik.
            // Kalau ternyata langsung diarahkan ke halaman personal, lanjut saja.
            if ($browser->element('.profil-edit-btn')) {
                $browser->click('.profil-edit-btn')
                    ->pause(3000)
                    ->screenshot('04-setelah-klik-edit-profil');
            } else {
                $browser->visit('http://127.0.0.1:8000/dokter/profile/personal')
                    ->pause(3000)
                    ->screenshot('04-langsung-halaman-personal');
            }

            $browser->assertPathIs('/dokter/profile/personal')
                ->assertSee('Mohon Isi Informasi Data Diri Dokter dengan Lengkap')

                // Update data diri dokter
                ->type('@doctor-name', 'ucing automation')
                ->type('@doctor-age', '25')
                ->type('@doctor-phone', '6287789243999')
                ->type('@doctor-weight', '50')
                ->type('@doctor-height', '155')
                ->click('@doctor-gender-male')
                ->type('@doctor-country', 'Indonesia')
                ->type('@doctor-city', 'Bandung')
                ->type('@doctor-postal-code', '40111')

                ->click('@doctor-save-profile')
                ->pause(4000)
                ->screenshot('05-setelah-update-data-diri')

                // Setelah data diri berhasil, harus masuk halaman keahlian
                ->assertPathIs('/dokter/profile/expertise')

                // Lanjut dari halaman keahlian ke halaman sertifikasi
                ->press('Simpan & Lanjut ke Sertifikasi')
                ->pause(4000)
                ->screenshot('06-setelah-simpan-keahlian')

                ->assertPathIs('/dokter/profile/certification')

                // Lanjut dari sertifikasi sampai selesai
                ->press('Selanjutnya')
                ->pause(5000)
                ->screenshot('07-setelah-update-profile-selesai')

                // Berdasarkan controller, setelah sertifikasi berhasil diarahkan ke dashboard
                ->assertPathIs('/dokter/dashboard');
        });
    }
}