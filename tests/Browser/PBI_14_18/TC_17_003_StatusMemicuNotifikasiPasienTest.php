<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_17_003_StatusMemicuNotifikasiPasienTest extends Pbi14To18DuskTestCase
{
    public function test_tc_17_003_perubahan_status_mengirim_notifikasi_ke_pasien(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->subDays(3)->toDateString(),
            'queue_number' => 43,
            'status' => 'Menunggu',
            'complaint' => 'TC.17.003 notification on status update',
        ]);
        $this->recordNotificationCleanup($patient['id'], 'Status Jadwal Temu Diperbarui');

        $this->browse(function (Browser $browser) use ($appointment): void {
            $this->loginAsDoctor($browser);
            $browser->visit($this->appUrl('/dokter/riwayat'))
                ->waitForText('Riwayat', 10);

            $buttonSelector = '.btn-lihat-catatan[data-appointment-id="'.$appointment['id'].'"]';
            $this->waitForElement($browser, $buttonSelector);
            $this->clickElement($browser, $buttonSelector);
            $browser->script('window.showToast = function (message) { window.__lastPbi17Toast = message; };');
            $this->clickElement($browser, '#btn-akhiri-sesi');

            $browser->waitUsing(10, 100, function () use ($browser): bool {
                return (bool) ($browser->script("return document.querySelector('#drawer-status-pill')?.textContent.includes('Selesai');")[0] ?? false);
            }, 'Status drawer tidak berubah menjadi Selesai.');

            $this->loginAsPatient($browser);
            $this->clickElement($browser, '#notificationButton');

            $browser->waitUsing(5, 100, function () use ($browser): bool {
                return (bool) ($browser->script("return !document.querySelector('#notificationPanel')?.classList.contains('hidden');")[0] ?? false);
            }, 'Panel notifikasi tidak terbuka.');

            $browser->assertSee('Status Jadwal Temu Diperbarui')
                ->assertSee('Selesai');
        });
    }
}
