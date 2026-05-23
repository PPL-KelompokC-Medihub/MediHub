<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginDokterNegativeTest extends DuskTestCase
{
    /**
     * Test login gagal karena password salah
     */
    public function test_login_dokter_password_salah(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-dokter')

                    ->pause(3000)

                    ->type('@login-email', 'revafedayeen@gmail.com')

                    ->type('@login-password', 'passwordSALAH')

                    ->press('@login-button')

                    ->pause(5000)

                    ->assertPathIs('/login-dokter');
        });
    }
}