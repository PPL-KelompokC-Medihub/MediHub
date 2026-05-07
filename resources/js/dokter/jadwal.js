/**
 * Doctor Schedule Management
 * Menangani create, edit, delete jadwal dengan modal form dan delete confirmation
 */

document.addEventListener('DOMContentLoaded', function() {
    const scheduleRoot = document.querySelector('[data-doctor-schedule]');
    if (!scheduleRoot) return;

    const storeUrl = scheduleRoot.dataset.storeUrl;
    const baseUrl = scheduleRoot.dataset.baseUrl;
    const modal = document.getElementById('modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const inputTanggal = document.getElementById('input-tanggal');
    const inputJamMulai = document.getElementById('input-jam-mulai');
    const inputJamSelesai = document.getElementById('input-jam-selesai');
    const modalSubmit = document.getElementById('modal-submit');

    let currentEditId = null;

    /* ─── MODAL: OPEN CREATE ─── */
    document.querySelectorAll('[data-schedule-modal="create"]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentEditId = null;
            modalTitle.textContent = 'Buat Jadwal Baru';
            modalSubmit.textContent = 'Simpan Jadwal';
            inputTanggal.value = '';
            inputJamMulai.value = '';
            inputJamSelesai.value = '';
            modal.classList.add('is-open');
        });
    });

    /* ─── MODAL: OPEN EDIT ─── */
    document.querySelectorAll('[data-schedule-modal="edit"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.scheduleId;
            const tanggal = btn.dataset.tanggal;
            const jamMulai = btn.dataset.jamMulai;
            const jamSelesai = btn.dataset.jamSelesai;

            currentEditId = id;
            modalTitle.textContent = 'Edit Jadwal';
            modalSubmit.textContent = 'Update Jadwal';
            inputTanggal.value = tanggal;
            inputJamMulai.value = jamMulai;
            inputJamSelesai.value = jamSelesai;
            modal.classList.add('is-open');
        });
    });

    /* ─── MODAL: CLOSE ─── */
    document.querySelectorAll('[data-schedule-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('is-open');
        });
    });

    /* ─── MODAL: SUBMIT (CREATE/EDIT) ─── */
    modalSubmit.addEventListener('click', async () => {
        const tanggal = inputTanggal.value;
        const jamMulai = inputJamMulai.value;
        const jamSelesai = inputJamSelesai.value;

        if (!tanggal || !jamMulai || !jamSelesai) {
            alert('Semua field harus diisi!');
            return;
        }

        const payload = {
            tanggal,
            jam_mulai: jamMulai,
            jam_selesai: jamSelesai,
        };

        if (currentEditId) {
            payload._method = 'PUT';
            payload.id = currentEditId;
        }

        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(payload),
            });

            if (response.ok) {
                modal.classList.remove('is-open');
                window.location.reload();
            } else {
                alert('Terjadi kesalahan saat menyimpan jadwal');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan jadwal');
        }
    });

    /* ─── DELETE: CUSTOM CONFIRMATION DIALOG ─── */
    document.querySelectorAll('[data-schedule-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.dataset.scheduleDelete;
            showDeleteConfirmation(id);
        });
    });

    function showDeleteConfirmation(scheduleId) {
        // Create overlay
        const confirmOverlay = document.createElement('div');
        confirmOverlay.className = 'doctor-schedule-delete-overlay';

        // Create dialog
        const confirmDialog = document.createElement('div');
        confirmDialog.className = 'doctor-schedule-delete-dialog';

        confirmDialog.innerHTML = `
            <h3 class="doctor-schedule-delete-title">Apakah Anda yakin ingin menghapus jadwal temu?</h3>
            <p class="doctor-schedule-delete-subtitle">Anda tidak bisa membatalkan aksi ini</p>
            <div class="doctor-schedule-delete-actions">
                <button class="doctor-delete-btn doctor-delete-cancel" type="button">
                    Batalkan
                </button>
                <button class="doctor-delete-btn doctor-delete-confirm" type="button">
                    Ya
                </button>
            </div>
        `;

        confirmOverlay.appendChild(confirmDialog);
        document.body.appendChild(confirmOverlay);

        const cancelBtn = confirmDialog.querySelector('.doctor-delete-cancel');
        const confirmBtn = confirmDialog.querySelector('.doctor-delete-confirm');

        // Handle cancel
        cancelBtn.addEventListener('click', () => {
            confirmOverlay.remove();
        });

        // Handle confirm delete
        confirmBtn.addEventListener('click', async () => {
            try {
                const response = await fetch(`${baseUrl}/${scheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                if (response.ok) {
                    confirmOverlay.remove();
                    window.location.reload();
                } else {
                    alert('Terjadi kesalahan saat menghapus jadwal');
                    confirmOverlay.remove();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus jadwal');
                confirmOverlay.remove();
            }
        });

        // Close on overlay click
        confirmOverlay.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) {
                confirmOverlay.remove();
            }
        });
    }
});