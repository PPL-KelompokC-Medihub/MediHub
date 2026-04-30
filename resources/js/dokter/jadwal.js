const scheduleRoot = document.querySelector('[data-doctor-schedule]');

if (scheduleRoot) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storeUrl = scheduleRoot.dataset.storeUrl;
    const baseUrl = scheduleRoot.dataset.baseUrl;
    let currentMode = 'create';
    let currentId = null;

    const openModal = (mode, id = null, tanggal = '', jamMulai = '', jamSelesai = '') => {
        currentMode = mode;
        currentId = id;

        document.getElementById('modal-title').textContent = mode === 'create' ? 'Buat Jadwal Baru' : 'Edit Jadwal';
        document.getElementById('modal-submit').textContent = mode === 'create' ? 'Simpan Jadwal' : 'Simpan Perubahan';
        document.getElementById('input-tanggal').value = tanggal;
        document.getElementById('input-jam-mulai').value = jamMulai;
        document.getElementById('input-jam-selesai').value = jamSelesai;
        document.getElementById('modal-overlay').classList.add('is-open');
    };

    const closeModal = () => {
        document.getElementById('modal-overlay').classList.remove('is-open');
    };

    const submitForm = async () => {
        const tanggal = document.getElementById('input-tanggal').value;
        const jamMulai = document.getElementById('input-jam-mulai').value;
        const jamSelesai = document.getElementById('input-jam-selesai').value;

        if (!tanggal || !jamMulai || !jamSelesai) {
            alert('Semua field harus diisi!');
            return;
        }

        const url = currentMode === 'create' ? storeUrl : `${baseUrl}/${currentId}`;
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
    };

    const confirmDelete = async (id) => {
        if (!confirm('Hapus jadwal ini?')) return;

        const res = await fetch(`${baseUrl}/${id}`, {
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
    };

    scheduleRoot.addEventListener('click', (event) => {
        const modalTrigger = event.target.closest('[data-schedule-modal]');
        if (modalTrigger) {
            openModal(
                modalTrigger.dataset.scheduleModal,
                modalTrigger.dataset.scheduleId || null,
                modalTrigger.dataset.tanggal || '',
                modalTrigger.dataset.jamMulai || '',
                modalTrigger.dataset.jamSelesai || '',
            );
            return;
        }

        const deleteTrigger = event.target.closest('[data-schedule-delete]');
        if (deleteTrigger) {
            confirmDelete(deleteTrigger.dataset.scheduleDelete);
            return;
        }

        if (event.target.closest('[data-schedule-close]')) {
            closeModal();
            return;
        }

        if (event.target.closest('[data-schedule-submit]')) {
            submitForm();
        }
    });

    document.getElementById('modal-overlay')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) closeModal();
    });
}
