<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_17_001_DokterMengubahStatusPasienTest extends Pbi14To18DuskTestCase
{
    public function test_tc_17_001_dokter_mengubah_status_pasien_menjadi_selesai(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->subDay()->toDateString(),
            'queue_number' => 41,
            'status' => 'Menunggu',
            'complaint' => 'TC.17.001 status update',
        ]);
        $this->recordNotificationCleanup($patient['id'], 'Status Jadwal Temu Diperbarui');

        $this->browse(function (Browser $browser) use ($appointment): void {
            $this->loginAsDoctor($browser);
            $browser->visit($this->appUrl('/dokter/riwayat'))
                ->waitForText('Riwayat', 10);

            $buttonSelector = '.btn-lihat-catatan[data-appointment-id="'.$appointment['id'].'"]';
            $this->waitForElement($browser, $buttonSelector);
            $this->clickElement($browser, $buttonSelector);

            $browser->waitUsing(5, 100, function () use ($browser): bool {
                return (bool) ($browser->script("return document.querySelector('#details-drawer')?.classList.contains('is-open');")[0] ?? false);
            }, 'Drawer catatan medis tidak terbuka.');

            $browser->assertSee('Catatan Medis')
                ->assertSee('Akhiri Sesi');

            $browser->script('window.showToast = function (message) { window.__lastPbi17Toast = message; };');
            $this->clickElement($browser, '#btn-akhiri-sesi');

            $browser->waitUsing(10, 100, function () use ($browser): bool {
                return (bool) ($browser->script("return document.querySelector('#drawer-status-pill')?.textContent.includes('Selesai');")[0] ?? false);
            }, 'Status drawer tidak berubah menjadi Selesai.');
        });

        $updated = $this->firestore()->find('BuatJadwalTemu', (string) $appointment['id']);
        $this->assertSame('selesai', strtolower((string) ($updated['status'] ?? '')));
    }
}
