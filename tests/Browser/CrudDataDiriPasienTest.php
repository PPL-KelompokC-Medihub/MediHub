<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CrudDataDiriPasienTest extends DuskTestCase
{
    public function test_update_data_diri_pasien_berhasil(): void
    {
        $this->browse(function (Browser $browser) {

            $namaBaru = 'Pasien Automation Test';

            $browser->visit('http://127.0.0.1:8000/login-pasien')
                ->pause(3000)
                ->screenshot('pasien-01-halaman-login')

                ->type('@login-email', 'revafedayeen@gmail.com')
                ->type('@login-password', 'fedayeen123')
                ->click('@login-button')

                ->pause(8000)
                ->screenshot('pasien-02-setelah-login')

                ->visit('http://127.0.0.1:8000/pasien/profile')
                ->pause(4000)
                ->screenshot('pasien-03-halaman-profile')

                ->assertPathIs('/pasien/profile')
                ->assertSee('Informasi Pribadi')

                // Klik tombol Edit pada informasi pribadi
                ->click('#editProfileBtn')
                ->pause(1000)
                ->screenshot('pasien-04-setelah-klik-edit-profile');

            // Isi form informasi pribadi pasien
            $browser->script("
                function setField(name, value) {
                    const field = document.querySelector(`[name=\"\${name}\"]`);

                    if (field) {
                        field.removeAttribute('readonly');
                        field.removeAttribute('disabled');
                        field.value = value;
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                setField('fullname', '{$namaBaru}');
                setField('umur', '21');
                setField('weight', '55');
                setField('height', '165');
                setField('allergy_history', 'Tidak ada alergi');

                const gender = document.querySelector('input[name=\"gender\"][value=\"Pria\"]');
                if (gender) {
                    gender.removeAttribute('disabled');
                    gender.checked = true;
                    gender.dispatchEvent(new Event('change', { bubbles: true }));
                }

                const blood = document.querySelector('input[name=\"blood_type\"][value=\"O\"]');
                if (blood) {
                    blood.removeAttribute('disabled');
                    blood.checked = true;
                    blood.dispatchEvent(new Event('change', { bubbles: true }));
                }
            ");

            $browser->pause(1000)
                ->screenshot('pasien-05-form-profile-terisi');

            // Klik tombol Simpan pada form Informasi Pribadi
            $browser->script("
                const fullnameInput = document.querySelector('[name=\"fullname\"]');
                const form = fullnameInput ? fullnameInput.closest('form') : null;

                if (form) {
                    const submitButton = form.querySelector('button[type=\"submit\"]');
                    if (submitButton) {
                        submitButton.click();
                    } else {
                        form.submit();
                    }
                }
            ");

            $browser->pause(5000)
                ->screenshot('pasien-06-setelah-update-profile')

                ->assertPathIs('/pasien/profile')
                ->assertDontSee('Internal Server Error')
                ->assertDontSee('ConnectionException')
                ->assertDontSee('Operation timed out')
                ->assertSee('Informasi Pribadi');
        });
    }
}