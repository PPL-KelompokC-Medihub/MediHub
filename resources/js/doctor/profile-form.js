import { initDoctorOnboardingInteractions } from './onboarding-interactions';

document.addEventListener('DOMContentLoaded', () => {
    initDoctorOnboardingInteractions();

    const genderOptions = document.querySelectorAll('.gender-option');
    const genderInput = document.getElementById('doctor_gender');
    const addressHint = document.querySelector('.edit-btn.is-static');

    genderOptions.forEach((option) => {
        option.addEventListener('click', () => {
            genderOptions.forEach((item) => item.classList.remove('selected'));
            option.classList.add('selected');
            if (genderInput) {
                genderInput.value = option.dataset.gender || '';
            }
        });
    });

    addressHint?.addEventListener('click', () => {
        document.getElementById('doctor_country')?.focus();
    });

    document.querySelectorAll('.upload-box input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            const uploadBox = input.closest('.upload-box');
            const fileName = input.files?.[0]?.name || '';
            const label = uploadBox?.querySelector('span');

            uploadBox?.classList.toggle('has-file', fileName.length > 0);

            if (label && fileName) {
                label.textContent = fileName;
            }
        });
    });
});
