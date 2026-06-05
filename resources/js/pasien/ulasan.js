/**
 * PBI-23: CR Ulasan & Rating Layanan Dokter (Pasien)
 *
 * - Buka modal "Buat Ulasan" dengan pilih dokter, bintang rating, dan textarea
 * - Submit via AJAX POST ke /pasien/ulasan
 */

document.addEventListener('DOMContentLoaded', () => {
    const overlay      = document.getElementById('ulasan-overlay');
    const openBtn      = document.getElementById('btn-buat-ulasan');
    const closeBtn     = document.getElementById('ulasan-close');
    const cancelBtn    = document.getElementById('ulasan-cancel');
    const form         = document.getElementById('ulasan-form');
    const textarea     = document.getElementById('ulasan-text');
    const charCount    = document.getElementById('ulasan-char-count');
    const errorEl      = document.getElementById('ulasan-error');
    const submitBtn    = document.getElementById('ulasan-submit');
    const starsWrapper = document.getElementById('ulasan-stars');
    const ratingInput  = document.getElementById('ulasan-rating');

    if (!overlay) return;

    // ── BUKA / TUTUP MODAL ──────────────────────────────────
    function openModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        resetForm();
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);

    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });

    // ── STAR RATING ─────────────────────────────────────────
    let selectedRating = 0;

    starsWrapper?.querySelectorAll('.ulasan-star').forEach((star) => {
        const val = parseInt(star.dataset.value, 10);

        star.addEventListener('mouseover', () => highlightStars(val));
        star.addEventListener('mouseout',  () => highlightStars(selectedRating));
        star.addEventListener('click', () => {
            selectedRating    = val;
            ratingInput.value = val;
            highlightStars(val);
        });
    });

    function highlightStars(upTo) {
        starsWrapper?.querySelectorAll('.ulasan-star').forEach((s) => {
            const v = parseInt(s.dataset.value, 10);
            s.classList.toggle('active', v <= upTo);
        });
    }

    // ── CHAR COUNTER ─────────────────────────────────────────
    textarea?.addEventListener('input', () => {
        const len = textarea.value.length;
        if (charCount) charCount.textContent = `${len}/1000`;
    });

    // ── SUBMIT ───────────────────────────────────────────────
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError();

        const doctorId = document.getElementById('ulasan-doctor-id')?.value?.trim() ?? '';
        const rating   = parseInt(ratingInput?.value ?? '0', 10);
        const text     = textarea?.value?.trim() ?? '';

        // Validasi client-side
        if (!doctorId) { showError('Pilih dokter terlebih dahulu.'); return; }
        if (rating < 1 || rating > 5) { showError('Pilih rating bintang terlebih dahulu.'); return; }
        if (text.length < 10) { showError('Ulasan harus minimal 10 karakter.'); return; }

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Menyimpan...';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ doctor_id: doctorId, rating, ulasan: text }),
            });

            const data = await res.json();

            if (data.success) {
                closeModal();
                showToast('Ulasan berhasil ditambahkan! 🎉');
            } else {
                showError(data.message ?? 'Gagal menyimpan ulasan.');
            }
        } catch {
            showError('Terjadi kesalahan. Silakan coba lagi.');
        } finally {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Kirim Ulasan';
        }
    });

    // ── HELPERS ──────────────────────────────────────────────
    function resetForm() {
        form?.reset();
        selectedRating    = 0;
        ratingInput.value = 0;
        highlightStars(0);
        if (charCount) charCount.textContent = '0/1000';
        hideError();
    }

    function showError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.classList.add('show');
    }

    function hideError() {
        errorEl?.classList.remove('show');
    }

    function showToast(msg, isError = false) {
        document.getElementById('ulasan-toast')?.remove();
        const toast         = document.createElement('div');
        toast.id            = 'ulasan-toast';
        toast.className     = 'ulasan-toast' + (isError ? ' error' : '');
        toast.textContent   = msg;
        document.body.appendChild(toast);
        requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 350); }, 3500);
    }
});
