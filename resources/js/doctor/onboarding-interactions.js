// Shared micro-interactions for doctor onboarding pages.
// Keeps the visual design intact while making the experience feel smoother.
export function initDoctorOnboardingInteractions() {
    const body = document.body;
    const illustrationImage = document.querySelector('.illustration-container img');
    const revealTargets = document.querySelectorAll('.form-alert, .stepper, .form-section, .footer-action');
    const interactiveControls = document.querySelectorAll(
        '.btn-primary, .gender-option, .service-pill, .edit-btn, .step-item, input, textarea'
    );

    revealTargets.forEach((element, index) => {
        element.style.setProperty('--enter-delay', `${index * 70}ms`);
    });

    requestAnimationFrame(() => {
        body.classList.add('doctor-ui-ready');
        body.classList.remove('doctor-ui-pending');
        revealTargets.forEach((element) => element.classList.add('is-visible'));
    });

    if (illustrationImage) {
        const markImageReady = () => illustrationImage.classList.add('is-loaded');

        if (illustrationImage.complete) {
            markImageReady();
        } else {
            illustrationImage.addEventListener('load', markImageReady, { once: true });
        }
    }

    interactiveControls.forEach((control) => {
        control.addEventListener('pointerdown', () => {
            control.classList.add('is-pressed');
        });

        const clearPressedState = () => control.classList.remove('is-pressed');
        control.addEventListener('pointerup', clearPressedState);
        control.addEventListener('pointerleave', clearPressedState);
        control.addEventListener('blur', clearPressedState);
    });

    const fieldGroups = document.querySelectorAll('.form-group');
    fieldGroups.forEach((group) => {
        const field = group.querySelector('input, textarea');

        field?.addEventListener('focus', () => group.classList.add('is-focused'));
        field?.addEventListener('blur', () => group.classList.remove('is-focused'));
    });
}
