import { initDoctorOnboardingInteractions } from './onboarding-interactions';

document.addEventListener('DOMContentLoaded', () => {
    initDoctorOnboardingInteractions();

    const servicePills = document.querySelectorAll('.service-pill');

    servicePills.forEach((pill) => {
        const checkbox = pill.querySelector('input[type="checkbox"]');

        if (!checkbox) {
            return;
        }

        pill.addEventListener('click', () => {
            pill.classList.toggle('selected', checkbox.checked);
        });

        checkbox.addEventListener('change', () => {
            pill.classList.toggle('selected', checkbox.checked);
        });
    });
});
