document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('reviewModal');
    const openButtons = document.querySelectorAll('[data-review-open]');
    const closeButtons = document.querySelectorAll('[data-review-close]');
    const notificationCenter = document.getElementById('notificationCenter');
    const notificationOverlay = document.getElementById('notificationOverlay');
    const notificationOpenButtons = document.querySelectorAll('[data-notification-open]');
    const notificationCloseButtons = document.querySelectorAll('[data-notification-close]');

    const openReviewModal = () => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('reviewText')?.focus();
    };

    const closeReviewModal = () => {
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openReviewModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeReviewModal);
    });

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeReviewModal();
        }
    });

    const openNotificationCenter = () => {
        if (!notificationCenter || !notificationOverlay) {
            return;
        }

        notificationOverlay.classList.remove('hidden');
        notificationCenter.classList.remove('hidden');
        notificationCenter.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        window.requestAnimationFrame(() => {
            notificationCenter.classList.remove('translate-x-6', 'opacity-0');
            notificationCenter.classList.add('translate-x-0', 'opacity-100');
        });
    };

    const closeNotificationCenter = () => {
        if (!notificationCenter || !notificationOverlay) {
            return;
        }

        notificationCenter.classList.add('translate-x-6', 'opacity-0');
        notificationCenter.classList.remove('translate-x-0', 'opacity-100');
        notificationOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            if (notificationCenter.classList.contains('opacity-0')) {
                notificationCenter.classList.add('hidden');
                notificationCenter.classList.remove('flex');
            }
        }, 180);
    };

    notificationOpenButtons.forEach((button) => {
        button.addEventListener('click', openNotificationCenter);
    });

    notificationCloseButtons.forEach((button) => {
        button.addEventListener('click', closeNotificationCenter);
    });

    // Edit Review Modal
    const editModal = document.getElementById('editReviewModal');
    const editForm = document.getElementById('editReviewForm');
    const editButtons = document.querySelectorAll('[data-edit-review-btn]');
    const closeEditButtons = document.querySelectorAll('[data-edit-review-close]');

    const openEditReviewModal = (event) => {
        if (!editModal || !editForm) {
            return;
        }

        const button = event.currentTarget;
        const reviewId = button.dataset.id;
        const rating = button.dataset.rating;
        const text = button.dataset.text;

        // Set action dynamically
        editForm.action = `/pasien/layanan/ulasan/${reviewId}`;

        // Set rating radio checked
        const ratingInput = document.getElementById(`editRating${Math.round(parseFloat(rating))}`);
        if (ratingInput) {
            ratingInput.checked = true;
        }

        // Set text
        const textarea = document.getElementById('editReviewText');
        if (textarea) {
            textarea.value = text;
        }

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        textarea?.focus();
    };

    const closeEditReviewModal = () => {
        if (!editModal) {
            return;
        }
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    };

    editButtons.forEach((button) => {
        button.addEventListener('click', openEditReviewModal);
    });

    closeEditButtons.forEach((button) => {
        button.addEventListener('click', closeEditReviewModal);
    });

    editModal?.addEventListener('click', (event) => {
        if (event.target === editModal) {
            closeEditReviewModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        if (modal && !modal.classList.contains('hidden')) {
            closeReviewModal();
        }

        if (editModal && !editModal.classList.contains('hidden')) {
            closeEditReviewModal();
        }

        if (notificationCenter && !notificationCenter.classList.contains('hidden')) {
            closeNotificationCenter();
        }
    });

    if (document.querySelector('[data-review-open-on-load]')) {
        openReviewModal();
    }
});
