@extends('layouts.landing')

@section('content')
        <!-- Background SVG -->
        <div class="hero-bg"></div>

        <!-- ══ NAVBAR ══ -->
        <header>
            <a href="/" class="logo" style="text-decoration: none;">
                <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" style="height: 32px; width: auto;" />
            </a>
            <nav>
                <div class="login-dropdown">
                    <div class="login-dropbtn">
                        <span>Login</span>
                        <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    <div class="login-dropdown-content">
                        <a href="{{ route('login-dokter') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Dokter
                        </a>
                        <a href="/sign-in?role=patient">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Pasien
                        </a>
                    </div>
                </div>
                <a href="{{ route('register-dokter') }}" class="btn-signup" style="display: flex; align-items: center; gap: 8px; background: var(--primary, #6aa4ef); color: white; padding: 10px 24px; border-radius: 999px; font-weight: 600; font-size: 14px; text-decoration: none; box-shadow: 0 4px 12px rgba(106, 164, 239, 0.25); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(106, 164, 239, 0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(106, 164, 239, 0.25)';">
                    <span>Daftar Gratis</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </nav>
        </header>

        <!-- ══ HERO ══ -->
        <div class="hero-content">
            <h1>Jembatan Menuju</h1>
            <h1 class="gradient-blue-text">Layanan Kesehatan Terbaik</h1>
            <p>Sahabat Akses Kesehatan</p>
            <div class="hero-btns">
                <button class="cta-btn cta-btn-blue">
                    <span>Hubungi Kami</span>
                    <span class="cta-icon cta-icon-blue">
                        <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/vuesax-linear-arrow-right.svg"
                            alt="arrow" />
                    </span>
                </button>
                <button class="cta-btn cta-btn-white">
                    <span>Sign up</span>
                    <span class="cta-icon cta-icon-gray">
                        <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/vuesax-linear-arrow-right.svg"
                            alt="arrow" />
                    </span>
                </button>
            </div>
        </div>

        <!-- ══ DOCTOR BANNER PHOTO ══ -->
        <div class="doctor-image">
            <img src="{{ asset('images/dokter-landing-page.png') }}" alt="Dokter" />
        </div>

        <!-- ══ TENTANG MEDIHUB HEADING ══ -->
        <div class="tentang-heading">
            <span class="tentang gradient-dark-text">Tentang</span>
            <div class="mediq-logo-inline">
                <img src="{{ asset('images/medihub-logo.svg') }}" alt="MediHub" style="height: 36px; width: auto;" />
            </div>
        </div>

        <!-- ══ ABOUT DESCRIPTION ══ -->
        <div class="about-desc">
            <p>MediQ hadir sebagai solusi layanan kesehatan digital yang memudahkan pasien untuk terhubung dengan
                dokter, rumah sakit, dan fasilitas kesehatan terpercaya. Kami berkomitmen untuk memberikan pengalaman
                konsultasi dan layanan kesehatan yang mudah, cepat, dan aman.</p>
        </div>

        <!-- ══ STATS ══ -->
        <div class="stats-section">
            <div class="stats-inner">
                <div class="stat-item">
                    <div class="stat-value gradient-dark-text">50.000+</div>
                    <div class="stat-label">Pengguna Terdaftar</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item w153">
                    <div class="stat-value gradient-dark-text">500+</div>
                    <div class="stat-label">Dokter Mitra dari Berbagai Spesialis</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item w169">
                    <div class="stat-value gradient-dark-text">120+</div>
                    <div class="stat-label">Fasilitas Kesehatan Terpecaya</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item w153">
                    <div class="stat-value gradient-dark-text">98%</div>
                    <div class="stat-label">Tingkat Kepuasan Pasien</div>
                </div>
            </div>
        </div>

        <!-- ══ SERVICE HIGHLIGHTS ══ -->
        <section class="service-highlights">
            <div class="service-left">
                <h2 style="font-size: 32px; font-weight: 500; color: #1a1e26; margin: 0 0 32px; line-height: 1.2;">
                    Layanan<br>
                    <span style="color: #6aa4ef; font-weight: 400;">Terpopular Kami</span>
                </h2>
                <a href="#" class="btn-primary-sm">
                    <span>Telusuri</span>
                    <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/vuesax-linear-arrow-right-1.svg" alt="arrow" />
                </a>
            </div>
            <div class="service-grid-v3">
                <!-- Kandungan -->
                <div class="service-card-v3">
                    <div class="service-header-v3">
                        <img src="{{ asset('images/service-kandungan.png') }}" alt="Kandungan" class="icon-v3" />
                        <h3>Kandungan</h3>
                    </div>
                    <p>Kami menyediakan layanan kesehatan bagi ibu hamil dan wanita, mulai dari pemeriksaan rutin, konsultasi kehamilan, hingga perawatan kesehatan reproduksi dengan dukungan dokter yang berpengalaman.</p>
                </div>
                
                <!-- Gigi & Mulut -->
                <div class="service-card-v3">
                    <div class="service-header-v3">
                        <img src="{{ asset('images/service-gigi.png') }}" alt="Gigi & Mulut" class="icon-v3" />
                        <h3>Gigi & Mulut</h3>
                    </div>
                    <p>Kami memberikan perawatan gigi dan mulut secara menyeluruh, mulai dari pemeriksaan rutin, pembersihan, penambalan, hingga perawatan lanjutan untuk menjaga senyum sehat Anda.</p>
                </div>

                <!-- Umum -->
                <div class="service-card-v3">
                    <div class="service-header-v3">
                        <img src="{{ asset('images/service-umum.png') }}" alt="Umum" class="icon-v3" />
                        <h3>Umum</h3>
                    </div>
                    <p>Kami menyediakan layanan pemeriksaan kesehatan dasar dengan penuh kepedulian untuk membantu Anda menjaga kondisi tubuh, mencegah penyakit, serta mendapatkan penanganan medis awal yang tepat.</p>
                </div>

                <!-- Anak -->
                <div class="service-card-v3">
                    <div class="service-header-v3">
                        <img src="{{ asset('images/service-anak.png') }}" alt="Anak" class="icon-v3" />
                        <h3>Anak</h3>
                    </div>
                    <p>Kami memberikan layanan kesehatan khusus anak dengan penuh perhatian dan kasih sayang, mulai dari pemeriksaan rutin, imunisasi, hingga penanganan penyakit agar tumbuh kembang si kecil tetap optimal.</p>
                </div>
            </div>
        </section>

        <!-- ══ DOCTORS SECTION ══ -->
        <div style="text-align: center; margin-top: -20px; margin-bottom: 24px;">
            <h2 style="font-size: 28px; font-weight: 600; color: #1a1e26; margin-bottom: 8px;">Dokter Unggulan</h2>
            <h3 class="gradient-blue-text" style="font-size: 28px; font-weight: 500;">Siap Membantu Anda</h3>
        </div>
        <section class="doctors-section" style="margin-top: 0; padding-top: 20px;">

            <!-- Doctor 1 -->
            <div class="doctor-card">
                <img class="doctor-photo d1"
                    src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/493b8da7-21e7-44fb-a963-2889ed38bc63-removebg-preview-1.png"
                    alt="dr. Arief Nugroho" />

                <div class="doctor-info">
                    <div class="doctor-info-top">
                        <div class="doctor-name gradient-dark-text">
                            dr. Arief Nugroho, Sp.JP
                        </div>
                        <div class="doctor-specialty">
                            Spesialis Jantung dan Pembuluh Darah
                        </div>
                    </div>

                    <div class="doctor-meta">
                        <div class="doctor-rating">
                            <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/material-symbols-star-rounded.svg"
                                alt="star" />
                            5.0
                        </div>
                        <div class="meta-divider"></div>
                        <div class="doctor-patients">450+ Total Pasien</div>
                    </div>
                </div>
            </div>

            <!-- Doctor 2 -->
            <div class="doctor-card">
                <img class="doctor-photo d2"
                    src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/1d01911b-ab2c-4ed7-8cb6-98d738ffe783-removebg-preview-1.png"
                    alt="dr. Ratna Dewi" />

                <div class="doctor-info">
                    <div class="doctor-info-top">
                        <div class="doctor-name gradient-dark-text">
                            dr. Ratna Dewi, Sp.A
                        </div>
                        <div class="doctor-specialty">Spesialis Anak</div>
                    </div>

                    <div class="doctor-meta">
                        <div class="doctor-rating">
                            <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/material-symbols-star-rounded.svg"
                                alt="star" />
                            4.9
                        </div>
                        <div class="meta-divider"></div>
                        <div class="doctor-patients">450+ Total Pasien</div>
                    </div>
                </div>
            </div>

            <!-- Doctor 3 -->
            <div class="doctor-card">
                <img class="doctor-photo d3"
                    src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/76a31ddc-a3f4-4a2b-848c-9847e95e71dd-removebg-preview--1--1.png"
                    alt="dr. Clara Wulandari" />

                <div class="doctor-info">
                    <div class="doctor-info-top">
                        <div class="doctor-name gradient-dark-text">
                            dr. Clara Wulandari, M.Ked
                        </div>
                        <div class="doctor-specialty">Dokter Umum</div>
                    </div>

                    <div class="doctor-meta">
                        <div class="doctor-rating">
                            <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/material-symbols-star-rounded.svg"
                                alt="star" />
                            4.9
                        </div>
                        <div class="meta-divider"></div>
                        <div class="doctor-patients">450+ Total Pasien</div>
                    </div>
                </div>
            </div>

            <!-- Doctor 4 -->
            <div class="doctor-card">
                <img class="doctor-photo d4"
                    src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/--lekarz--pediatra--neurolog--urodzi-a-micha-a-w--removebg-previ.png"
                    alt="dr. Andini Pratama" />

                <div class="doctor-info">
                    <div class="doctor-info-top">
                        <div class="doctor-name gradient-dark-text">
                            dr. Andini Pratama, Sp.PD
                        </div>
                        <div class="doctor-specialty">
                            Spesialis Penyakit Dalam
                        </div>
                    </div>

                    <div class="doctor-meta">
                        <div class="doctor-rating">
                            <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/material-symbols-star-rounded.svg"
                                alt="star" />
                            5.0
                        </div>
                        <div class="meta-divider"></div>
                        <div class="doctor-patients">450+ Total Pasien</div>
                    </div>
                </div>
            </div>

        </section>

        <!-- ══ FOOTER ══ -->
        <footer>

            <div class="footer-top">

                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="{{ asset('images/medihub-logo.svg') }}" alt="MediHub" style="height: 32px; width: auto;" />
                    </div>

                    <p class="footer-tagline">
                        Jembatan Menuju Layanan Kesehatan Terbaik
                    </p>
                </div>

                <div class="footer-nav">

                    <div class="footer-col">
                        <span class="footer-col-title">Navigasi</span>
                        <div class="footer-links">
                            <a href="#" class="footer-link">Home</a>
                            <a href="#" class="footer-link">Tentang Kami</a>
                            <a href="#" class="footer-link">Kontak</a>
                        </div>
                    </div>

                    <div class="footer-col">
                        <span class="footer-col-title">Informasi</span>
                        <div class="footer-links">
                            <a href="#" class="footer-link">Instagram</a>
                            <a href="#" class="footer-link">Twitter</a>
                            <a href="#" class="footer-link">Facebook</a>
                        </div>
                    </div>

                    <div class="footer-col">
                        <span class="footer-col-title">Mitra Kerja</span>
                        <div class="footer-links">
                            <a href="#" class="footer-link">Kemenkes</a>
                            <a href="#" class="footer-link">BPJS</a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-social">
                <img src="https://c.animaapp.com/mo3i2i8wgsBU2q/img/frame-240121.svg" alt="Social media icons" />
            </div>
        </footer>
@endsection