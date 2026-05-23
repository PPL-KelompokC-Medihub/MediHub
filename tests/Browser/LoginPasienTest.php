<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginPasienTest extends DuskTestCase
{
    /**
     * Test login pasien berhasil
     */
    public function test_login_pasien_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-pasien')

                    ->pause(3000)

                    ->type('@login-email', 'revafedayeen@gmail.com')

                    ->type('@login-password', 'fedayeen123')

                    ->press('@login-button')

                    ->pause(5000)

                    ->screenshot('login-pasien-berhasil');
        });
    }
}