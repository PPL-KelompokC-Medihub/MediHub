<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_15_001_NotifikasiMunculSetelahAksiTest extends Pbi14To18DuskTestCase
{
    public function test_tc_15_001_notifikasi_terbaru_muncul_setelah_aksi(): void
    {
        $patient = $this->patientIdentity();
        $suffix = uniqid('tc15001-', false);
        $title = "Notifikasi Dusk {$suffix}";
        $message = "Pesan notifikasi hasil aksi {$suffix}";

        $this->createNotification($patient, $title, $message, Carbon::now());

        $this->browse(function (Browser $browser) use ($title, $message): void {
            $this->loginAsPatient($browser);
            $this->openNotificationPanel($browser);

            $browser->assertSee('Pusat Notifikasi')
                ->assertSee($title)
                ->assertSee($message);
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
