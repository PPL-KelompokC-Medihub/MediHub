<?php

namespace Tests\Browser\PBI_14_18;

use Laravel\Dusk\Browser;

class TC_15_003_TidakAdaNotifikasiTest extends Pbi14To18DuskTestCase
{
    public function test_tc_15_003_pasien_tanpa_notifikasi_melihat_daftar_kosong(): void
    {
        $email = trim((string) env('DUSK_EMPTY_PATIENT_EMAIL', ''));
        $password = trim((string) env('DUSK_EMPTY_PATIENT_PASSWORD', ''));

        if ($email === '' || $password === '') {
            $this->markTestSkipped('Set DUSK_EMPTY_PATIENT_EMAIL dan DUSK_EMPTY_PATIENT_PASSWORD untuk menjalankan empty-state notification test.');
        }

        $this->browse(function (Browser $browser) use ($email, $password): void {
            $this->loginAsPatient($browser, $email, $password);
            $this->openNotificationPanel($browser);

            $browser->assertSee('Belum ada notifikasi');
        });
    }

    private function openNotificationPanel(Browser $browser): void
    {
        $this->clickElement($browser, '#notificationButton');

        $browser->waitUsing(5, 100, function () use ($browser): bool {
            return (bool) ($browser->script("return !document.querySelector('#notificationPanel')?.classList.contains('hidden');")[0] ?? false);
        }, 'Panel notifikasi tidak terbuka.');
    }
}
