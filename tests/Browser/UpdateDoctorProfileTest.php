<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateDoctorProfileTest extends DuskTestCase
{
    public function test_update_profile_dokter_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('/login-dokter')
                    ->pause(5000)

                    ->screenshot('halaman-login');
        });
    }
}