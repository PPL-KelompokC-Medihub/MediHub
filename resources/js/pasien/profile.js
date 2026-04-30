const profileRoot = document.querySelector('[data-patient-profile]');

if (profileRoot) {
    const enableInputs = (triggerId, wrapperId, inputSelector, removeDisabled = false) => {
        const trigger = document.getElementById(triggerId);
        const wrapper = document.getElementById(wrapperId);
        const inputs = document.querySelectorAll(inputSelector);

        trigger?.addEventListener('click', () => {
            inputs.forEach((input) => {
                input.removeAttribute('readonly');

                if (removeDisabled) {
                    input.removeAttribute('disabled');
                }

                input.classList.add('bg-white', 'border-blue-300');
            });

            wrapper?.classList.remove('hidden');
            wrapper?.classList.add('flex');
            trigger.classList.add('hidden');
        });
    };

    enableInputs('editProfileBtn', 'saveProfileWrapper', '.profile-input', true);
    enableInputs('editAddressBtn', 'saveAddressWrapper', '.address-input');

    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.modalOpen)?.classList.remove('hidden');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.modalClose)?.classList.add('hidden');
        });
    });
}
