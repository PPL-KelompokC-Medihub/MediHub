<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegisterDokterTest extends DuskTestCase
{
    public function test_register_dokter_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $randomEmail = 'dokter'.rand(1000,9999).'@gmail.com';

            $browser->visit('http://127.0.0.1:8000/register-dokter')

                    ->pause(3000)

                    ->assertSee('Mulai Perjalanan Sehatmu Bersama MediHub')

                    ->type('@register-name', 'Dokter Testing')

                    ->type('@register-email', $randomEmail)

                    ->type('@register-password', 'password123')

                    ->type('@register-password-confirmation', 'password123')

                    ->press('@register-button')

                    ->pause(5000)

                    ->screenshot('register-berhasil');
        });
    }
}