<?php

namespace Tests\Browser\PBI_14_18;

use Carbon\Carbon;
use Laravel\Dusk\Browser;

class TC_18_003_PasienMelihatCatatanMedisResepTest extends Pbi14To18DuskTestCase
{
    public function test_tc_18_003_pasien_melihat_catatan_medis_dan_resep(): void
    {
        $doctor = $this->doctorIdentity();
        $patient = $this->patientIdentity();
        $appointment = $this->createDoctorAppointment($doctor, $patient, [
            'appointment_date' => Carbon::now()->subDay()->toDateString(),
            'queue_number' => 53,
            'status' => 'Selesai',
            'complaint' => 'TC.18.003 keluhan pasien',
        ]);

        $diagnosis = 'TC.18.003 hasil asesmen pasien terlihat';
        $note = 'TC.18.003 catatan medis pasien terlihat';
        $prescription = 'TC.18.003 resep obat pasien terlihat';

        $this->createMedicalNote([
            'appointment_id' => (string) $appointment['id'],
            'doctor_id' => $doctor['id'],
            'doctor_user_id' => $doctor['user_id'],
            'doctor_name' => $doctor['name'],
            'patient_id' => $patient['id'],
            'patient_name' => $patient['name'],
            'keluhan_utama' => 'TC.18.003 keluhan utama',
            'hasil_observasi' => 'TC.18.003 observasi',
            'hasil_asesmen' => $diagnosis,
            'kesimpulan' => 'TC.18.003 kesimpulan',
            'rekomendasi' => $note,
            'resep_obat' => $prescription,
            'created_at' => Carbon::now()->toIso8601String(),
        ]);

        $this->browse(function (Browser $browser) use ($diagnosis, $note, $prescription): void {
            $this->loginAsPatient($browser);
            $browser->visit($this->appUrl('/pasien/diagnosa'))
                ->waitForText('Hasil diagnosa', 10);

            $browser->waitUsing(30, 2000, function () use ($browser, $diagnosis): bool {
                if (str_contains($browser->text('body'), $diagnosis)) {
                    return true;
                }

                $browser->refresh()->waitForText('Hasil diagnosa', 10);

                return str_contains($browser->text('body'), $diagnosis);
            }, "Catatan medis {$diagnosis} tidak muncul di halaman pasien.");

            $expanded = $browser->script(<<<JS
                const card = Array.from(document.querySelectorAll('.diagnosis-card'))
                    .find((el) => el.dataset.diagnosa === {$this->jsonForScript($diagnosis)} || el.textContent.includes({$this->jsonForScript($diagnosis)}));
                card?.scrollIntoView({ block: 'center' });
                card?.querySelector('[data-toggle-detail]')?.click();
                return Boolean(card);
            JS);

            $this->assertTrue((bool) ($expanded[0] ?? false));
            $browser->waitForText($note, 10)
                ->assertSee($prescription);
        });
    }

    private function jsonForScript(string $value): string
    {
        return (string) json_encode($value, JSON_THROW_ON_ERROR);
    }
}
