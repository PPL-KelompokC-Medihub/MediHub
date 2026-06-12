<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_15_002_UrutanNotifikasiTerbaruDiAtasTest extends Pbi14To18DuskTestCase
{
    public function test_tc_15_002_daftar_notifikasi_diurutkan_dari_yang_terbaru(): void
    {
        $patient = $this->patientIdentity();
        $suffix = uniqid('tc15002-', false);
        $oldTitle = "Notifikasi Lama {$suffix}";
        $newTitle = "Notifikasi Baru {$suffix}";

        $this->createNotification($patient, $oldTitle, 'Notifikasi lama untuk cek sorting.', Carbon::now()->subHours(2));
        $this->createNotification($patient, $newTitle, 'Notifikasi baru untuk cek sorting.', Carbon::now()->subMinute());

        $this->browse(function (Browser $browser) use ($oldTitle, $newTitle): void {
            $this->loginAsPatient($browser);
            $this->openNotificationPanel($browser);

            $titles = $this->panelNotificationTitles($browser);
            $newIndex = array_search($newTitle, $titles, true);
            $oldIndex = array_search($oldTitle, $titles, true);

            $this->assertNotFalse($newIndex, 'Notifikasi baru tidak ditemukan di panel.');
            $this->assertNotFalse($oldIndex, 'Notifikasi lama tidak ditemukan di panel.');
            $this->assertLessThan($oldIndex, $newIndex, 'Notifikasi terbaru harus tampil di atas notifikasi lama.');
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
