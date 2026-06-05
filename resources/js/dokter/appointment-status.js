/**
 * PBI-17: Update Status Pasien oleh Dokter
 *
 * Mengirim PATCH request ke endpoint /dokter/appointment/{id}/status
 * lalu memperbarui badge status di tabel tanpa reload halaman.
 */

const STATUS_CLASSES = {
    menunggu:   'doctor-status-menunggu',
    diperiksa:  'doctor-status-diperiksa',
    selesai:    'doctor-status-selesai',
    dibatalkan: 'doctor-status-dibatalkan',
};

const STATUS_LABELS = {
    menunggu:  'Menunggu',
    diperiksa: 'Diperiksa',
    selesai:   'Selesai',
};

/**
 * Dipanggil saat dokter mengubah pilihan di dropdown status.
 * @param {HTMLSelectElement} selectEl
 */
window.updatePatientStatus = function (selectEl) {
    const appointmentId = selectEl.dataset.appointmentId;
    const updateUrl     = selectEl.dataset.updateUrl;
    const newStatus     = selectEl.value;

    // Disable selama proses request
    selectEl.disabled = true;

    fetch(updateUrl, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: newStatus }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                updateStatusPill(appointmentId, data.new_status, data.status_label);
                showToast(`Status berhasil diubah menjadi ${data.status_label}.`);
            } else {
                showToast(data.message ?? 'Gagal mengubah status.', true);
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan. Silakan coba lagi.', true);
        })
        .finally(() => {
            selectEl.disabled = false;
        });
};

/**
 * Update tampilan badge/pill status di tabel pasien.
 */
function updateStatusPill(appointmentId, newStatus, label) {
    const pill = document.getElementById(`status-pill-${appointmentId}`);
    if (!pill) return;

    // Hapus semua class status lama
    Object.values(STATUS_CLASSES).forEach((cls) => pill.classList.remove(cls));

    // Tambah class status baru
    if (STATUS_CLASSES[newStatus]) {
        pill.classList.add(STATUS_CLASSES[newStatus]);
    }

    pill.textContent = label ?? STATUS_LABELS[newStatus] ?? newStatus;
}

/**
 * Tampilkan toast notification di pojok kanan bawah.
 * @param {string}  message
 * @param {boolean} isError
 */
function showToast(message, isError = false) {
    // Hapus toast lama kalau ada
    document.getElementById('doctor-status-toast')?.remove();

    const toast = document.createElement('div');
    toast.id        = 'doctor-status-toast';
    toast.className = 'doctor-status-toast' + (isError ? ' error' : '');
    toast.textContent = message;
    document.body.appendChild(toast);

    // Animasi masuk
    requestAnimationFrame(() => {
        requestAnimationFrame(() => toast.classList.add('show'));
    });

    // Hilang otomatis setelah 3 detik
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 3000);
}
