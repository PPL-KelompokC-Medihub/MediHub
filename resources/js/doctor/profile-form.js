document.addEventListener('DOMContentLoaded', () => {
    const genderOptions = document.querySelectorAll('.gender-option');
    const doctorProfileForm = document.getElementById('doctorProfileForm');

    genderOptions.forEach((option) => {
        option.addEventListener('click', () => {
            genderOptions.forEach((item) => item.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    doctorProfileForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        alert('Form submitted! Moving to next step...');
    });
});
