<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ScheduleDeleteTest extends DuskTestCase
{
    public function test_doctor_can_delete_schedule(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('http://127.0.0.1:8000/login-dokter')

                ->pause(3000)

                ->type('@login-email', 'revafedayeen@gmail.com')
                ->type('@login-password', 'fedayeen123')

                ->click('@login-button')

                ->pause(8000)

                ->screenshot('after-login-delete')

                ->assertPathIs('/dokter/dashboard')

                ->visit('http://127.0.0.1:8000/dokter/jadwal')

                ->pause(5000)

                ->assertSee('Jadwal Saya')

                ->waitFor('[dusk="delete-schedule-button"]', 10)

                ->click('[dusk="delete-schedule-button"]')

                ->pause(2000)

                ->assertSee('Apakah Anda yakin ingin menghapus jadwal temu?')

                ->waitForText('Apakah Anda yakin ingin menghapus jadwal temu?', 10)

                ->pause(1000)

                ->press('Ya')

                ->pause(5000)

                ->screenshot('schedule-delete-success')

                ->assertPathIs('/dokter/jadwal');
        });
    }
}