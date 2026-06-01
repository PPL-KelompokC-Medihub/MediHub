<?php

namespace Tests\Browser\PBI_26\Homepage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomepagebannerTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function test_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://127.0.0.1:8000/login-pasien')
                ->pause(3000)
                ->type('@login-email', 'naufalnr19@gmail.com')
                ->type('@login-password', '@Naufi06')
                ->click('@login-button')
                ->pause(3000)
                ->waitForLocation('/pasien/beranda', 10)
                ->assertPathIs('/pasien/beranda')
                ->screenshot('homepage-berhasil');
        });
    }
}
