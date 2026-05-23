<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginDokterTest extends DuskTestCase
{
    public function test_login_dokter_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-dokter')

                    ->pause(5000)

                    ->screenshot('halaman-login')

                    ->assertSee('Selamat Datang Kembali, Dokter!')

                    ->type('@login-email', 'revafedayeen@gmail.com')

                    ->type('@login-password', 'fedayeen123')

                    ->press('@login-button')

                    ->pause(5000);
        });
    }
}