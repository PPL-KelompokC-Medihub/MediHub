<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardDokterTest extends DuskTestCase
{
    public function test_dashboard_dokter_berhasil_ditampilkan(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-dokter')
                ->pause(3000)

                ->type('@login-email', 'revafedayeen@gmail.com')
                ->type('@login-password', 'fedayeen123')
                ->click('@login-button')

                ->pause(8000)

                ->screenshot('dashboard-dokter')

                ->assertPathIs('/dokter/dashboard')

                ->assertSee('Halo, dr')
                ->assertSee('Jadwal Saya')
                ->assertSee('Daftar Pasien')
                ->assertSee('Jadwal Temu Mendatang');
        });
    }
}