/**
 * Hasil Diagnosa & Catatan Medis — Pasien
 * ========================================
 * Handles: search, filter tabs, card expand, detail modal
 */
document.addEventListener('DOMContentLoaded', () => {

    /* ── Filter tabs ── */
    const filterTabs = document.querySelectorAll('.filter-tab-diagnosa');
    const diagnosisCards = document.querySelectorAll('[data-diagnosis-grid] .diagnosis-card');
    const searchInput = document.getElementById('searchDiagnosa');
    let activeFilter = 'semua';

    const applyFilters = () => {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        let visibleCount = 0;

        diagnosisCards.forEach(card => {
            const period = card.dataset.period || '';
            const text = card.textContent.toLowerCase();
            const matchesPeriod = activeFilter === 'semua' || period === activeFilter;
            const matchesSearch = searchTerm === '' || text.includes(searchTerm);

            // Cancel any pending animation timeout for this card
            if (card.timeoutId) {
                clearTimeout(card.timeoutId);
            }

            if (matchesPeriod && matchesSearch) {
                card.style.display = '';
                card.timeoutId = setTimeout(() => {
                    card.style.opacity = '1';
                }, 10);
                visibleCount++;
            } else {
                card.style.opacity = '0';
                card.timeoutId = setTimeout(() => {
                    card.style.display = 'none';
                }, 250);
            }
        });

        // Toggle empty state
        const emptyState = document.getElementById('emptyFilterState');
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
        }
    };

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            activeFilter = this.dataset.filter;

            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    /* ── Card expand / collapse ── */
    document.querySelectorAll('[data-toggle-detail]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.diagnosis-card');
            const detail = card.querySelector('.diagnosis-detail');

            if (!card || !detail) return;

            const isExpanded = card.classList.contains('expanded');

            // Collapse all others
            document.querySelectorAll('.diagnosis-card.expanded').forEach(other => {
                if (other !== card) {
                    other.classList.remove('expanded');
                    other.querySelector('.diagnosis-detail')?.classList.remove('show');
                }
            });

            if (isExpanded) {
                card.classList.remove('expanded');
                detail.classList.remove('show');
            } else {
                card.classList.add('expanded');
                detail.classList.add('show');
            }
        });
    });

    /* ── Detail modal ── */
    const modalOverlay = document.getElementById('diagnosisModalOverlay');
    const modal = document.getElementById('diagnosisModal');

    const openModal = (data) => {
        if (!modal || !modalOverlay) return;

        document.getElementById('modalDokter').textContent = data.dokter || '-';
        document.getElementById('modalSpesialis').textContent = data.spesialis || '-';
        document.getElementById('modalTanggal').textContent = data.tanggal || '-';
        document.getElementById('modalJam').textContent = data.jam || '-';
        document.getElementById('modalKeluhan').textContent = data.keluhan || 'Tidak ada keluhan tercatat';
        document.getElementById('modalDiagnosa').textContent = data.diagnosa || 'Belum ada hasil diagnosa';
        document.getElementById('modalCatatan').textContent = data.catatan || 'Tidak ada catatan medis';
        document.getElementById('modalResep').textContent = data.resep || 'Tidak ada resep obat';
        document.getElementById('modalRS').textContent = data.rs || '-';

        modalOverlay.classList.add('show');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        if (!modal || !modalOverlay) return;

        modal.classList.remove('show');
        modalOverlay.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const card = btn.closest('.diagnosis-card');
            if (!card) return;

            openModal({
                dokter: card.dataset.dokter,
                spesialis: card.dataset.spesialis,
                tanggal: card.dataset.tanggal,
                jam: card.dataset.jam,
                keluhan: card.dataset.keluhan,
                diagnosa: card.dataset.diagnosa,
                catatan: card.dataset.catatan,
                resep: card.dataset.resep,
                rs: card.dataset.rs,
            });
        });
    });

    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }

    document.getElementById('closeModalDiagnosa')?.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    /* ── Print button ── */
    document.getElementById('printDiagnosa')?.addEventListener('click', () => {
        window.print();
    });
});
