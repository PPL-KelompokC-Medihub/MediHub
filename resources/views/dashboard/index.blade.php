<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - MediHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="mediq-app-shell">
    <div class="mediq-shell-grid">

        {{-- SIDEBAR --}}
        <aside class="mediq-sidebar">
            <a href="{{ route('dashboard') }}" class="mediq-logo">
                <img src="{{ asset('images/medihub-logo.png') }}" alt="MediHub" />
            </a>

            <nav class="mediq-nav">
                <p class="mediq-nav-title">Menu</p>
                <a href="{{ route('dashboard') }}" class="mediq-nav-item is-active">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('jadwal.index') }}" class="mediq-nav-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    Jadwal Temu
                </a>
                <a href="#" class="mediq-nav-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat
                </a>
                <a href="{{ route('profile') }}" class="mediq-nav-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Profil
                </a>

            </nav>

            <div class="mediq-logout-wrap">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mediq-logout">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:10px">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="mediq-main">

            {{-- Header --}}
            <div class="mediq-header-row">
                <div class="mediq-user-chip">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($dokter->name ?? 'Dokter') }}&background=6aa4ef&color=fff&size=96"
                         alt="Avatar" class="mediq-avatar" />
                    <div>
                        <p class="mediq-user-name">Halo, dr {{ $dokter->name ?? Auth::user()->name ?? 'Dokter' }}</p>
                        <p style="margin:0;font-size:13px;color:var(--muted)">Bagaimana kabarmu?</p>
                    </div>
                </div>
                <div class="mediq-header-actions">
                    <div class="mediq-search-wrap">
                        <input type="text" class="mediq-search-input" placeholder="Cari..." />
                        <svg class="mediq-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </div>
                    <button class="mediq-icon-btn">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>
                </div>
            </div>


        {{-- Stats Cards --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
            <div style="background:linear-gradient(135deg,#deeeff 0%,#eef4ff 100%);border-radius:16px;padding:24px;position:relative;overflow:hidden">
                <p style="margin:0 0 12px;font-size:32px;font-weight:400;color:var(--primary-strong)">{{ $jadwalHariIni }}</p>
                <p style="margin:0 0 2px;font-size:14px;font-weight:400;color:#1f2024">Pasien Minggu Ini</p>
                <p style="margin:0;font-size:12px;color:var(--muted)">RS Medic Center - Bandung</p>
                <div style="position:absolute;right:-10px;bottom:-10px;opacity:0.15">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#deeeff 0%,#eef4ff 100%);border-radius:16px;padding:24px;position:relative;overflow:hidden">
                <p style="margin:0 0 12px;font-size:32px;font-weight:400;color:var(--primary-strong)">{{ count($jadwalSaya) }}</p>
                <p style="margin:0 0 2px;font-size:14px;font-weight:400;color:#1f2024">Jadwal Tersedia</p>
                <p style="margin:0;font-size:12px;color:var(--muted)">RS Medic Center - Bandung</p>
                <div style="position:absolute;right:-10px;bottom:-10px;opacity:0.15">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#deeeff 0%,#eef4ff 100%);border-radius:16px;padding:24px;position:relative;overflow:hidden">
                <p style="margin:0 0 12px;font-size:32px;font-weight:400;color:var(--primary-strong)">{{ $totalPasien }}</p>
                <p style="margin:0 0 2px;font-size:14px;font-weight:400;color:#1f2024">Pasien Aktif</p>
                <p style="margin:0;font-size:12px;color:var(--muted)">RS Medic Center - Bandung</p>
                <div style="position:absolute;right:-10px;bottom:-10px;opacity:0.15">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,#deeeff 0%,#eef4ff 100%);border-radius:16px;padding:24px;position:relative;overflow:hidden">
                <p style="margin:0 0 12px;font-size:32px;font-weight:400;color:var(--primary-strong)">{{ $totalPasien }}</p>
                <p style="margin:0 0 2px;font-size:14px;font-weight:400;color:#1f2024">Sesi Selesai Bulan Ini</p>
                <p style="margin:0;font-size:12px;color:var(--muted)">RS Medic Center - Bandung</p>
                <div style="position:absolute;right:-10px;bottom:-10px;opacity:0.15">
                    <svg width="80" height="80" fill="var(--primary)" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
        </div>


            {{-- Jadwal Saya --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h2 style="font-size:16px;font-weight:600;margin:0">Jadwal Saya</h2>
                    <a href="{{ route('jadwal.index') }}" class="mediq-primary-btn" style="display:flex;align-items:center;gap:8px;padding:8px 16px;font-size:13px;text-decoration:none">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Buat Jadwal Baru
                    </a>
                </div>
                @if(count($jadwalSaya) > 0)
                <div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:6px">
                    @foreach($jadwalSaya as $jadwal)
                    <div style="min-width:200px;border:1px solid var(--line);border-radius:14px;padding:14px;background:#fff">
                        <p style="margin:0 0 6px;font-size:12px;color:var(--primary);font-weight:600">Jadwal Tersedia</p>
                        <p style="margin:0 0 2px;font-size:13px;font-weight:700">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p style="margin:0;font-size:12px;color:var(--muted)">{{ $jadwal->jam_mulai ?? '-' }} - {{ $jadwal->jam_selesai ?? '-' }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="border:1px dashed var(--line);border-radius:14px;padding:24px;text-align:center;color:var(--muted)">
                    <p style="margin:0;font-size:14px">Belum ada jadwal tersedia. Buat jadwal baru!</p>
                </div>
                @endif
            </div>

            {{-- Daftar Pasien --}}
            <div>
                <h2 style="font-size:16px;font-weight:600;margin:0 0 12px">Daftar Pasien</h2>
                <div style="border:1px solid var(--line);border-radius:14px;overflow:hidden">
                    <table style="width:100%;border-collapse:collapse;font-size:14px">
                        <thead>
                            <tr style="background:#f8f9fb">
                                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--muted);font-size:13px">Pasien</th>
                                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--muted);font-size:13px">Tanggal</th>
                                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--muted);font-size:13px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                            <tr style="border-top:1px solid var(--line)">
                                <td style="padding:12px 16px;font-weight:500">{{ $appointment->patient_name ?? '-' }}</td>
                                <td style="padding:12px 16px;color:var(--muted)">{{ $appointment->appointment_date ?? '-' }}</td>
                                <td style="padding:12px 16px">
                                    <span style="background:#eef4ff;color:var(--primary-strong);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600">Aktif</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" style="padding:24px;text-align:center;color:var(--muted)">Belum ada pasien</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        {{-- RIGHT BAR --}}
        <aside class="mediq-rightbar">
            <div class="mediq-right-head">
                <h3 style="font-size:15px;font-weight:600;margin:0">Jadwal Temu Mendatang</h3>
            </div>

            <div style="display:grid;gap:10px">
                @forelse(array_slice($appointments, 0, 5) as $appointment)
                <div class="mediq-appointment-card">
                    <div class="mediq-appointment-head">
                        <div class="mediq-app-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="mediq-appointment-doctor">{{ $appointment->patient_name ?? 'Pasien' }}</p>
                    <div class="mediq-appointment-body">
                        <div class="mediq-app-queue">
                            <p class="mediq-muted">Antrian</p>
                            <p class="mediq-queue-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="mediq-app-datetime">
                            <p class="mediq-muted">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                                </svg>
                                {{ $appointment->appointment_date ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <p style="color:var(--muted);font-size:14px;text-align:center;padding:20px 0">Tidak ada jadwal mendatang</p>
                @endforelse
            </div>
        </aside>

    </div>
</div>
</body>
</html>