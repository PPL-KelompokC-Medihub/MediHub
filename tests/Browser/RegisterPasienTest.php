<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegisterPasienTest extends DuskTestCase
{
    /**
     * Test register pasien berhasil
     */
    public function test_register_pasien_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $randomEmail = 'pasien'.rand(1000,9999).'@gmail.com';

            $browser->visit('http://127.0.0.1:8000/register-pasien')

                    ->pause(3000)

                    ->type('@register-name', 'Pasien Testing')

                    ->type('@register-email', $randomEmail)

                    ->type('@register-password', 'password123')

                    ->type('@register-password-confirmation', 'password123')

                    ->press('@register-button')

                    ->pause(5000)

                    ->screenshot('register-pasien-berhasil');
        });
    }
}