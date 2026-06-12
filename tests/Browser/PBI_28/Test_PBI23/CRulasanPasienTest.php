<?php

namespace Tests\Browser\PBI_28\Test_PBI23;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class CRulasanPasienTest extends DuskTestCase
{
    private function loginAsPasien(Browser $browser, $email = 'myfesaaarofida@gmail.com')
    {
        $user = User::where('email', $email)->first();
        $browser->loginAs($user)
                ->visit('/pasien/layanan')
                ->waitForRoute('pasien.layanan', 10);
    }

    public function test_can_create_review_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('[data-review-open]', 10)
                    ->click('[data-review-open]')
                    ->waitFor('#reviewModal', 5)
                    ->click('input[name="rating"][value="4"]')
                    ->type('#reviewText', 'Dokter yang sangat profesional dan ramah. Pengalaman yang luar biasa!')
                    ->click('button[type="submit"]')
                    ->pause(1000)
                    ->assertNotVisible('#reviewModal')
                    ->assertSee('berhasil');
        });
    }

    public function test_cannot_submit_review_without_rating(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('[data-review-open]', 10)
                    ->click('[data-review-open]')
                    ->waitFor('#reviewModal', 5)
                    ->script('document.querySelectorAll("input[name=\"rating\"]").forEach(r => r.checked = false);');
            
            $browser->type('#reviewText', 'Ini adalah ulasan tanpa rating untuk testing')
                    ->click('button[type="submit"]')
                    ->pause(500)
                    ->assertVisible('#reviewModal'); // Harusnya masih nempel karena validasi
        });
    }

    public function test_cannot_submit_review_without_text(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('[data-review-open]', 10)
                    ->click('[data-review-open]')
                    ->waitFor('#reviewModal', 5)
                    ->click('input[name="rating"][value="5"]')
                    ->value('#reviewText', '') // Kosongkan
                    ->click('button[type="submit"]')
                    ->pause(500)
                    ->assertVisible('#reviewModal');
        });
    }

    public function test_can_edit_review(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->waitFor('[data-edit-review-btn]', 5)
                    ->click('[data-edit-review-btn]')
                    ->waitFor('#editReviewModal', 5)
                    ->value('#editReviewText', 'Ulasan yang sudah diupdate dengan informasi baru')
                    ->click('#editRating3')
                    ->click('button[type="submit"]')
                    ->pause(1000)
                    ->assertNotVisible('#editReviewModal');
        });
    }
}