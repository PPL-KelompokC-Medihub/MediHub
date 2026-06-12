<?php

namespace Tests\Browser\PBI_28\Test_PBI22;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class RiwayatPasienTest extends DuskTestCase
{
    // Helper untuk bypass login
    private function loginAsPasien(Browser $browser, $email = 'myfesaaarofida@gmail.com')
    {
        $user = User::where('email', $email)->first();
        $browser->loginAs($user)
                ->visit('/pasien/riwayat')
                ->waitForRoute('pasien.riwayat', 10);
    }

    public function test_can_view_appointment_history_cards(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->assertSee('Riwayat Jadwal Temu')
                    ->waitFor('[data-history-grid]', 10)
                    ->assertVisible('.appointment-card');
        });
    }

    public function test_can_view_appointment_history_detail(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('.appointment-card', 10)
                    ->click('.appointment-card:first-child')
                    ->pause(500)
                    ->assertVisible('#history-detail-panel')
                    ->assertVisible('#closeHistoryDetailBtn')
                    ->click('#closeHistoryDetailBtn')
                    ->pause(300)
                    ->assertNotVisible('#history-detail-panel');
        });
    }

    public function test_shows_empty_state_when_no_history(): void
    {
        $this->browse(function (Browser $browser) {
            // Gunakan akun pasien yang baru
            $this->loginAsPasien($browser, 'myfesaaarofida@gmail.com');
            
            $browser->assertSee('Belum ada riwayat jadwal temu')
                    ->assertVisible('a[href*="pasien/booking"]');
        });
    }

    public function test_can_search_appointment_history(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsPasien($browser);
            
            $browser->waitFor('.appointment-card', 10)
                    ->type('#searchHistory', 'Dr. Budi')
                    ->pause(500) // Beri waktu untuk filtering
                    ->assertVisible('.appointment-card');
        });
    }
}