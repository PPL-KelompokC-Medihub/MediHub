<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Temu - MediHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
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
                <a href="{{ route('dashboard') }}" class="mediq-nav-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('jadwal.index') }}" class="mediq-nav-item is-active">
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

        {{-- MAIN --}}
        <main class="mediq-main">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <h1 style="font-size:20px;font-weight:700;margin:0">Jadwal Saya</h1>
                <button onclick="openModal('create')" class="mediq-primary-btn" style="display:flex;align-items:center;gap:8px;padding:10px 18px">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Buat Jadwal Baru
                </button>
            </div>

            {{-- Minggu Ini --}}
            <div>
                <h2 style="font-size:15px;font-weight:600;margin:0 0 12px;color:var(--muted)">Minggu Ini</h2>
                @if(count($mingguIni) > 0)
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    @foreach($mingguIni as $j)
                    <div style="border:1px solid var(--line);border-radius:14px;padding:16px;background:#fff">
                        <p style="margin:0 0 4px;font-size:12px;color:var(--primary);font-weight:600">Jadwal Tersedia</p>
                        <p style="margin:0 0 2px;font-size:13px;font-weight:600">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p style="margin:0 0 14px;font-size:13px;color:var(--muted)">{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</p>
                        <div style="display:flex;gap:8px">
                            <button onclick="confirmDelete('{{ $j->id }}')"
                                style="border:1px solid var(--line);background:#fff;border-radius:8px;padding:6px 10px;cursor:pointer;color:var(--danger)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                </svg>
                            </button>
                            <button onclick="openModal('edit', '{{ $j->id }}', '{{ $j->tanggal }}', '{{ $j->jam_mulai }}', '{{ $j->jam_selesai }}')"
                                class="mediq-primary-btn" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:6px 12px;font-size:13px">
                                Edit
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="border:1px dashed var(--line);border-radius:14px;padding:20px;text-align:center;color:var(--muted);margin-bottom:16px">
                    Belum ada jadwal minggu ini
                </div>
                @endif
            </div>

            {{-- Minggu Depan --}}
            <div>
                <h2 style="font-size:15px;font-weight:600;margin:0 0 12px;color:var(--muted)">Minggu Depan</h2>
                @if(count($mingguDepan) > 0)
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    @foreach($mingguDepan as $j)
                    <div style="border:1px solid var(--line);border-radius:14px;padding:16px;background:#fff">
                        <p style="margin:0 0 4px;font-size:12px;color:var(--primary);font-weight:600">Jadwal Tersedia</p>
                        <p style="margin:0 0 2px;font-size:13px;font-weight:600">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</p>
                        <p style="margin:0 0 14px;font-size:13px;color:var(--muted)">{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</p>
                        <div style="display:flex;gap:8px">
                            <button onclick="confirmDelete('{{ $j->id }}')"
                                style="border:1px solid var(--line);background:#fff;border-radius:8px;padding:6px 10px;cursor:pointer;color:var(--danger)">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                </svg>
                            </button>
                            <button onclick="openModal('edit', '{{ $j->id }}', '{{ $j->tanggal }}', '{{ $j->jam_mulai }}', '{{ $j->jam_selesai }}')"
                                class="mediq-primary-btn" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:6px 12px;font-size:13px">
                                Edit
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H5a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="border:1px dashed var(--line);border-radius:14px;padding:20px;text-align:center;color:var(--muted)">
                    Belum ada jadwal minggu depan
                </div>
                @endif
            </div>
        </main>

        {{-- RIGHTBAR --}}
        <aside class="mediq-rightbar">
            <h3 style="font-size:15px;font-weight:600;margin:0 0 16px">Jadwal Temu Mendatang</h3>
            <p style="color:var(--muted);font-size:14px;text-align:center;padding:20px 0">Tidak ada jadwal mendatang</p>
        </aside>

    </div>
</div>

{{-- MODAL --}}
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:50;display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:20px;padding:32px;width:min(400px,90%);position:relative">
        <h2 id="modal-title" style="margin:0 0 20px;font-size:18px;font-weight:700"></h2>

        <div style="display:grid;gap:14px">
            <div>
                <label class="mediq-label">Tanggal</label>
                <input type="date" id="input-tanggal" class="mediq-input" />
            </div>
            <div>
                <label class="mediq-label">Jam Mulai</label>
                <input type="time" id="input-jam-mulai" class="mediq-input" />
            </div>
            <div>
                <label class="mediq-label">Jam Berakhir</label>
                <input type="time" id="input-jam-selesai" class="mediq-input" />
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px">
            <button onclick="closeModal()" style="flex:1;border:1px solid var(--line);background:#fff;border-radius:10px;padding:12px;cursor:pointer;font:inherit;font-size:14px">
                Batal
            </button>
            <button id="modal-submit" onclick="submitForm()" class="mediq-primary-btn" style="flex:1;padding:12px">
                Simpan Jadwal
            </button>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentMode = 'create';
    let currentId = null;

    function openModal(mode, id = null, tanggal = '', jamMulai = '', jamSelesai = '') {
        currentMode = mode;
        currentId = id;

        document.getElementById('modal-title').textContent = mode === 'create' ? 'Buat Jadwal Baru' : 'Edit Jadwal';
        document.getElementById('modal-submit').textContent = mode === 'create' ? 'Simpan Jadwal' : 'Simpan Perubahan';
        document.getElementById('input-tanggal').value = tanggal;
        document.getElementById('input-jam-mulai').value = jamMulai;
        document.getElementById('input-jam-selesai').value = jamSelesai;

        const overlay = document.getElementById('modal-overlay');
        overlay.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modal-overlay').style.display = 'none';
    }

    async function submitForm() {
        const tanggal = document.getElementById('input-tanggal').value;
        const jamMulai = document.getElementById('input-jam-mulai').value;
        const jamSelesai = document.getElementById('input-jam-selesai').value;

        if (!tanggal || !jamMulai || !jamSelesai) {
            alert('Semua field harus diisi!');
            return;
        }

        const url = currentMode === 'create'
            ? '{{ route("jadwal.store") }}'
            : `/jadwal-dokter/${currentId}`;

        const method = currentMode === 'create' ? 'POST' : 'PUT';

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ tanggal, jam_mulai: jamMulai, jam_selesai: jamSelesai }),
        });

        if (res.ok) {
            closeModal();
            window.location.reload();
        } else {
            alert('Gagal menyimpan jadwal!');
        }
    }

    async function confirmDelete(id) {
        if (!confirm('Hapus jadwal ini?')) return;

        const res = await fetch(`/jadwal-dokter/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        if (res.ok) {
            window.location.reload();
        } else {
            alert('Gagal menghapus jadwal!');
        }
    }

    // Close modal when clicking outside
    document.getElementById('modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>