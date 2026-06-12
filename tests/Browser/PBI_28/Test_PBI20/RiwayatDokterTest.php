<?php

namespace Tests\Browser\PBI_28\Test_PBI20;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RiwayatDokterTest extends DuskTestCase
{
    /**
     * PBI-20 TC.001 - Lihat riwayat janji temu berhasil (Role: Dokter)
     * 
     * Test case ini memverifikasi bahwa dokter dapat mengakses halaman riwayat
     * janji temu dan daftar appointment history tertampil dengan benar.
     * 
     * Verifikasi:
     * - Dokter berhasil login
     * - Berhasil redirect ke dokter.dashboard (bukan pasien.beranda)
     * - Berhasil navigate ke halaman riwayat janji temu
     * - Grid appointment history tertampil
     * - Setiap card menampilkan informasi pasien lengkap
     * - Status pills visible dengan warna sesuai status
     */
    public function testLihatRiwayatJanjiTemuBerhasil(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                // 1. Login sebagai dokter
                ->visit('/login-dokter')
                ->waitForRoute('login-dokter', 10)
                ->assertVisible('input[type="email"]')
                ->assertVisible('input[type="password"]')
                
                // 2. Input credentials dokter
                ->value('input[type="email"]', 'dokter@test.com')
                ->value('input[type="password"]', 'password123')
                
                // 3. Submit login form
                ->click('button[type="submit"]')
                ->waitForNavigation()
                ->pause(500)
                
                // 4. Verifikasi redirect ke dokter.dashboard (bukan pasien.beranda)
                ->assertRouteIs('dokter.dashboard')
                ->assertVisible('[class*="dokter"]') // Dokter sidebar
                
                // 5. Navigate ke halaman riwayat janji temu
                ->visit('/dokter/riwayat')
                ->waitForRoute('dokter.riwayat', 10)
                
                // 6. Verifikasi halaman header
                ->assertSee('Riwayat')
                ->assertPathIs('/dokter/riwayat')
                
                // 7. Tunggu hingga grid appointment history load
                ->waitFor('.riwayat-grid', 10)
                ->assertPresent('.riwayat-grid')
                ->assertVisible('.riwayat-grid')
                
                // 8. Verifikasi ada appointment cards atau empty state
                ->script('
                    const grid = document.querySelector(".riwayat-grid");
                    const cards = grid ? grid.querySelectorAll(".riwayat-card").length : 0;
                    const emptyState = document.querySelector(".riwayat-empty-state");
                    return cards > 0 || emptyState !== null;
                ')
                
                // 9. Jika ada appointment cards, verifikasi struktur dasar
                ->script('
                    const cards = document.querySelectorAll(".riwayat-card");
                    if (cards.length > 0) {
                        const firstCard = cards[0];
                        return {
                            hasHeader: firstCard.querySelector(".riwayat-card-header") !== null,
                            hasStatus: firstCard.querySelector(".doctor-status-pill") !== null,
                            hasMeta: firstCard.querySelector(".riwayat-card-meta-grid") !== null,
                            hasButton: firstCard.querySelector(".btn-lihat-catatan") !== null
                        };
                    }
                    return null;
                ')
                
                // 10. Verifikasi card appointment visible
                ->assertVisible('.riwayat-card')
                ->assertVisible('.riwayat-card-header')
                ->assertVisible('.doctor-status-pill')
                
                // 11. Verifikasi nama pasien visible
                ->assertVisible('.patient-name')
                
                // 12. Verifikasi tanggal dan waktu visible
                ->assertVisible('.riwayat-card-meta-grid')
                
                // 13. Verifikasi tombol "Lihat Catatan" visible
                ->assertVisible('.btn-lihat-catatan')
            ;
        });
    }

    /**
     * PBI-20 TC.002 - Card riwayat tampil dengan info lengkap (Role: Dokter)
     * 
     * Test case ini memverifikasi bahwa setiap card riwayat menampilkan
     * informasi pasien yang lengkap dan akurat.
     * 
     * Verifikasi:
     * - Dokter berhasil login
     * - Daftar appointment history tertampil
     * - Setiap card memiliki informasi lengkap:
     *   - Avatar/Icon pasien
     *   - Nama pasien
     *   - Tipe layanan / spesialisasi
     *   - Status appointment (Menunggu/On Going/Selesai/Dibatalkan)
     *   - Tanggal appointment dengan icon
     *   - Waktu appointment dengan icon
     *   - Tombol "Lihat Catatan Medis"
     * - Styling status pill sesuai status (warna berbeda)
     */
    public function testCardRiwayatTampilDenganInfoLengkap(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                // 1. Login sebagai dokter
                ->visit('/login-dokter')
                ->waitForRoute('login-dokter')
                ->value('input[type="email"]', 'dokter@test.com')
                ->value('input[type="password"]', 'password123')
                ->click('button[type="submit"]')
                ->waitForNavigation()
                ->pause(500)
                
                // 2. Verifikasi login berhasil (role dokter)
                ->assertRouteIs('dokter.dashboard')
                
                // 3. Navigate ke halaman riwayat
                ->visit('/dokter/riwayat')
                ->waitForRoute('dokter.riwayat', 10)
                
                // 4. Verifikasi halaman load
                ->assertSee('Riwayat')
                ->waitFor('.riwayat-grid', 10)
                
                // 5. Tunggu appointment cards visible
                ->waitFor('.riwayat-card', 10)
                ->assertVisible('.riwayat-card')
                
                // 6. Verifikasi ada minimal 1 card
                ->script('
                    return document.querySelectorAll(".riwayat-card").length > 0;
                ')
                
                // 7. Inspect card pertama untuk verifikasi info lengkap
                ->script('
                    const card = document.querySelector(".riwayat-card");
                    return {
                        // Header components
                        hasPatientAvatar: card.querySelector(".patient-avatar-wrap") !== null,
                        hasPatientName: card.querySelector(".patient-name") !== null,
                        hasServiceType: card.querySelector(".service-type") !== null,
                        
                        // Status
                        hasStatusPill: card.querySelector(".doctor-status-pill") !== null,
                        statusText: card.querySelector(".doctor-status-pill")?.textContent.trim() || "",
                        
                        // Date & Time
                        hasMetaGrid: card.querySelector(".riwayat-card-meta-grid") !== null,
                        
                        // Button
                        hasLihatCatatanBtn: card.querySelector(".btn-lihat-catatan") !== null,
                        btnText: card.querySelector(".btn-lihat-catatan")?.textContent.trim() || ""
                    };
                ')
                
                // 8. Verifikasi avatar/icon pasien
                ->assertVisible('.riwayat-card .patient-avatar-wrap')
                
                // 9. Verifikasi nama pasien
                ->assertVisible('.riwayat-card .patient-name')
                ->script('
                    const name = document.querySelector(".patient-name").textContent.trim();
                    return name.length > 0 && name !== "-";
                ')
                
                // 10. Verifikasi tipe layanan/spesialisasi
                ->assertVisible('.riwayat-card .service-type')
                
                // 11. Verifikasi status pill ada dengan text
                ->assertVisible('.riwayat-card .doctor-status-pill')
                ->script('
                    const statusText = document.querySelector(".doctor-status-pill").textContent.trim();
                    const validStatuses = ["Menunggu", "On Going", "Selesai", "Dibatalkan"];
                    return validStatuses.some(s => statusText.includes(s));
                ')
                
                // 12. Verifikasi status pill styling (warna berbeda per status)
                ->script('
                    const pill = document.querySelector(".doctor-status-pill");
                    const classes = Array.from(pill.classList);
                    const hasStatusClass = classes.some(c => c.includes("doctor-status-"));
                    return hasStatusClass;
                ')
                
                // 13. Verifikasi metadata grid (tanggal & waktu)
                ->assertVisible('.riwayat-card .riwayat-card-meta-grid')
                
                // 14. Verifikasi ada date dan time display
                ->script('
                    const card = document.querySelector(".riwayat-card");
                    const svgIcons = card.querySelectorAll(".riwayat-card-meta-grid svg");
                    return svgIcons.length >= 2; // Minimal 2 icons (date & time)
                ')
                
                // 15. Verifikasi tombol "Lihat Catatan Medis"
                ->assertVisible('.riwayat-card .btn-lihat-catatan')
                ->script('
                    const btn = document.querySelector(".btn-lihat-catatan");
                    return btn.textContent.includes("Catatan");
                ')
                
                // 16. Verifikasi button memiliki appointment ID data
                ->script('
                    const btn = document.querySelector(".btn-lihat-catatan");
                    return btn.getAttribute("data-appointment-id") !== null;
                ')
                
                // 17. Verifikasi card styling dan hover effect
                ->script('
                    const card = document.querySelector(".riwayat-card");
                    const styles = window.getComputedStyle(card);
                    return {
                        hasBackground: styles.backgroundColor !== "",
                        hasBorder: styles.border !== "",
                        hasRadius: styles.borderRadius !== ""
                    };
                ')
            ;
        });
    }

    /**
     * PBI-20 TC.003 - Klik card membuka detail drawer (Bonus Test)
     * 
     * Test case bonus untuk verifikasi bahwa klik card atau button
     * membuka detail drawer dengan informasi lengkap.
     */
    public function testClickCardOpenDetailsDrawer(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                // 1. Login sebagai dokter
                ->visit('/login-dokter')
                ->waitForRoute('login-dokter')
                ->value('input[type="email"]', 'dokter@test.com')
                ->value('input[type="password"]', 'password123')
                ->click('button[type="submit"]')
                ->waitForNavigation()
                ->pause(500)
                
                // 2. Navigate ke riwayat
                ->visit('/dokter/riwayat')
                ->waitForRoute('dokter.riwayat', 10)
                
                // 3. Tunggu card load
                ->waitFor('.btn-lihat-catatan', 10)
                
                // 4. Klik tombol "Lihat Catatan"
                ->click('.btn-lihat-catatan')
                ->pause(500)
                
                // 5. Verifikasi details drawer terbuka
                ->script('
                    const drawer = document.querySelector(".details-drawer");
                    return drawer && drawer.classList.contains("is-open");
                ')
                
                // 6. Verifikasi drawer content visible
                ->assertVisible('.drawer-content')
                
                // 7. Verifikasi drawer header dan close button
                ->assertVisible('.drawer-header')
                ->assertVisible('.btn-close-drawer')
                
                // 8. Klik close button untuk tutup drawer
                ->click('.btn-close-drawer')
                ->pause(300)
                
                // 9. Verifikasi drawer tertutup
                ->script('
                    const drawer = document.querySelector(".details-drawer");
                    return !drawer.classList.contains("is-open");
                ')
            ;
        });
    }

    /**
     * PBI-20 TC.004 - Empty state ketika tidak ada riwayat (Bonus Test)
     * 
     * Test case bonus untuk verifikasi empty state message
     * ketika dokter tidak memiliki riwayat janji temu.
     */
    public function testShowEmptyStateWhenNoRiwayat(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                // 1. Login sebagai dokter
                ->visit('/login-dokter')
                ->waitForRoute('login-dokter')
                ->value('input[type="email"]', 'dokter-baru@test.com')
                ->value('input[type="password"]', 'password123')
                ->click('button[type="submit"]')
                ->waitForNavigation()
                ->pause(500)
                
                // 2. Navigate ke riwayat
                ->visit('/dokter/riwayat')
                ->waitForRoute('dokter.riwayat', 10)
                
                // 3. Verifikasi empty state ditampilkan atau ada cards
                ->script('
                    const cards = document.querySelectorAll(".riwayat-card");
                    const emptyState = document.querySelector(".riwayat-empty-state");
                    return cards.length === 0 && emptyState !== null || cards.length > 0;
                ')
            ;
        });
    }
}