<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ScheduleCreateTest extends DuskTestCase
{
    private string $baseUrl = 'http://127.0.0.1:8000';
    private string $email = 'revafedayeen@gmail.com';
    private string $password = 'fedayeen123';

    private function loginAsDokter(Browser $browser): void
    {
        $browser->visit($this->baseUrl . '/login-dokter')
            ->pause(2000)

            ->type('@login-email', $this->email)
            ->type('@login-password', $this->password)

            ->click('@login-button')
            ->pause(8000)

            ->screenshot('after-login-dashboard')

            ->assertPathIs('/dokter/dashboard');

        echo "\nDashboard URL: " . $browser->driver->getCurrentURL() . "\n";

        $browser->visit($this->baseUrl . '/dokter/jadwal')
            ->pause(5000)

            ->screenshot('after-open-jadwal');

        echo "\nJadwal URL: " . $browser->driver->getCurrentURL() . "\n";

        $browser->assertPathIs('/dokter/jadwal')
            ->assertSee('Jadwal Saya');
    }

    private function fillScheduleForm(
        Browser $browser,
        string $date,
        string $startTime,
        string $endTime
    ): void {
        $browser->script("
            const dateInput = document.querySelector('[dusk=\"schedule-date\"]');
            const startInput = document.querySelector('[dusk=\"schedule-start-time\"]');
            const endInput = document.querySelector('[dusk=\"schedule-end-time\"]');

            if (dateInput) dateInput.value = '{$date}';
            if (startInput) startInput.value = '{$startTime}';
            if (endInput) endInput.value = '{$endTime}';

            [dateInput, startInput, endInput].forEach((input) => {
                if (input) {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        ");

        $browser->pause(1000);
    }

    public function test_doctor_can_create_and_read_schedule(): void
    {
        $date = Carbon::today()->addDays(3)->format('Y-m-d');
        $startTime = '08:15';
        $endTime = '09:15';

        $this->browse(function (Browser $browser) use ($date, $startTime, $endTime) {

            $this->loginAsDokter($browser);

            $browser->assertSee('Minggu Ini')
                ->assertSee('Minggu Depan')

                ->waitFor('[dusk="create-schedule-button"]', 10)
                ->assertPresent('[dusk="create-schedule-button"]')

                ->click('[dusk="create-schedule-button"]')
                ->pause(3000)

                ->screenshot('before-create-schedule')

                ->assertSee('Buat Jadwal Baru');

            $this->fillScheduleForm(
                $browser,
                $date,
                $startTime,
                $endTime
            );

            $browser->waitFor('[dusk="save-schedule-button"]', 10)
                ->click('[dusk="save-schedule-button"]')
                ->pause(5000)

                ->screenshot('after-save-schedule')

                ->assertPathIs('/dokter/jadwal')
                ->assertSee('Jadwal Saya');
        });
    }
}