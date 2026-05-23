<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginPasienNegativeTest extends DuskTestCase
{
    /**
     * Test login pasien gagal karena password salah
     */
    public function test_login_pasien_password_salah(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-pasien')

                    ->pause(3000)

                    ->type('@login-email', 'revafedayeen@gmail.com')

                    ->type('@login-password', 'passwordSALAH')

                    ->press('@login-button')

                    ->pause(5000)

                    ->assertPathIs('/login-pasien')

                    ->screenshot('login-pasien-password-salah');
        });
    }
}