// Riwayat Jadwal Temu JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Toggle Cancel Buttons in upcoming schedules
    const toggleCancelBtn = document.getElementById('toggleCancelBtn');
    const cancelBtnContainers = document.querySelectorAll('.cancel-btn-container');

    if (toggleCancelBtn) {
        toggleCancelBtn.addEventListener('click', function () {
            cancelBtnContainers.forEach(container => {
                container.classList.toggle('hidden');
            });

            if (toggleCancelBtn.textContent.trim() === 'Batalkan') {
                toggleCancelBtn.textContent = 'Selesai';
                toggleCancelBtn.classList.remove('text-[#58A7F7]');
                toggleCancelBtn.classList.add('text-gray-500');
            } else {
                toggleCancelBtn.textContent = 'Batalkan';
                toggleCancelBtn.classList.remove('text-gray-500');
                toggleCancelBtn.classList.add('text-[#58A7F7]');
            }
        });
    }

    // Search filter logic for History Cards
    const appointmentCards = document.querySelectorAll('[data-history-grid] .appointment-card');
    const searchInput = document.getElementById('searchHistory');

    const applySearch = () => {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        appointmentCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const matchesSearch = searchTerm === '' || text.includes(searchTerm);

            if (matchesSearch) {
                card.style.display = 'block';
                setTimeout(() => card.style.opacity = '1', 0);
            } else {
                card.style.opacity = '0';
                setTimeout(() => card.style.display = 'none', 200);
            }
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', applySearch);
    }
});
