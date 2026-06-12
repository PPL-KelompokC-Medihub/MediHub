<?php

namespace Tests\Browser\PBI_28\Test_PBI24;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class UDulasanPasienTest extends DuskTestCase
{
    private function loginAsPasien(Browser $browser, $email = 'myfesaaarofida@gmail.com')
    {
        $user = User::where('email', $email)->first();
        $browser->loginAs($user)
                ->visit('/pasien/layanan')
                ->waitForRoute('pasien.layanan', 10);
    }

    public function test_can_update_review_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->click('[data-edit-review-btn]')
                    ->waitFor('#editReviewModal', 5)
                    ->click('#editRating3')
                    ->type('#editReviewText', 'Ulasan yang sudah diupdate dengan informasi yang lebih lengkap dan detail.')
                    ->click('#editReviewForm button[type="submit"]')
                    ->pause(1000)
                    ->assertNotVisible('#editReviewModal')
                    ->assertSee('berhasil');
        });
    }

    public function test_cannot_update_review_with_empty_text(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->click('[data-edit-review-btn]')
                    ->waitFor('#editReviewModal', 5)
                    ->clear('#editReviewText') // Cara lebih bersih daripada value('', '')
                    ->click('#editReviewForm button[type="submit"]')
                    ->pause(500)
                    ->assertVisible('#editReviewModal'); // Harus tetap terbuka karena validasi
        });
    }

    public function test_can_delete_review_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->click('article:first-child form button[type="submit"]')
                    ->acceptDialog() // Gunakan acceptDialog() untuk confirm box
                    ->pause(1000)
                    ->assertSee('berhasil');
        });
    }

    public function test_can_cancel_edit_review(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->click('[data-edit-review-btn]')
                    ->waitFor('#editReviewModal', 5)
                    ->type('#editReviewText', 'Input ini akan dibatalkan')
                    ->click('[data-edit-review-close]')
                    ->pause(500)
                    ->assertNotVisible('#editReviewModal');
        });
    }

    public function test_can_cancel_delete_review(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('article', 10)
                    ->click('article:first-child form button[type="submit"]')
                    ->dismissDialog() // Membatalkan alert konfirmasi
                    ->pause(500)
                    ->assertVisible('article'); // Pastikan artikel masih ada
        });
    }
}