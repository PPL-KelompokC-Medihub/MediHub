document.addEventListener('DOMContentLoaded', function () {
    // =========================
    // EDIT INFORMASI PRIBADI
    // =========================
    const editProfileBtn = document.getElementById('editProfileBtn');
    const profileInputs = document.querySelectorAll('.profile-input');
    const saveProfileWrapper = document.getElementById('saveProfileWrapper');

    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function () {
            profileInputs.forEach(function (input) {
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
            });

            saveProfileWrapper.classList.remove('hidden');
            saveProfileWrapper.classList.add('flex');
        });
    }

    const cancelProfileBtn = document.getElementById('cancelProfileBtn');

    if (cancelProfileBtn) {

        cancelProfileBtn.addEventListener('click', function () {

            profileInputs.forEach(function (input) {

                if (
                    input.type !== 'radio' &&
                    input.type !== 'checkbox'
                ) {
                    input.setAttribute('readonly', true);
                }

                if (
                    input.type === 'radio' ||
                    input.type === 'checkbox'
                ) {
                    input.setAttribute('disabled', true);
                }
            });

            saveProfileWrapper.classList.add('hidden');
            saveProfileWrapper.classList.remove('flex');
        });
    }

    // =========================
    // EDIT ALAMAT
    // =========================
    const editAddressBtn = document.getElementById('editAddressBtn');
    const addressInputs = document.querySelectorAll('.address-input');
    const saveAddressWrapper = document.getElementById('saveAddressWrapper');

    if (editAddressBtn) {
        editAddressBtn.addEventListener('click', function () {
            addressInputs.forEach(function (input) {
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
            });

            saveAddressWrapper.classList.remove('hidden');
            saveAddressWrapper.classList.add('flex');
        });
    }

    const cancelAddressBtn = document.getElementById('cancelAddressBtn');

    if (cancelAddressBtn) {

        cancelAddressBtn.addEventListener('click', function () {

            addressInputs.forEach(function (input) {
                input.setAttribute('readonly', true);
            });

            saveAddressWrapper.classList.add('hidden');
            saveAddressWrapper.classList.remove('flex');
        });
    }

    // =========================
    // FOTO PROFIL + CROP
    // =========================
    const choosePhotoBtn = document.getElementById('choosePhotoBtn');
    const profilePictInput = document.getElementById('profilePictInput');
    const cropModal = document.getElementById('cropModal');
    const cropPreview = document.getElementById('cropPreview');
    const cancelCropBtn = document.getElementById('cancelCropBtn');
    const saveCropBtn = document.getElementById('saveCropBtn');
    const croppedImageInput = document.getElementById('croppedImageInput');
    const profilePhotoForm = document.getElementById('profilePhotoForm');

    let cropper = null;

    if (choosePhotoBtn) {
        choosePhotoBtn.addEventListener('click', function () {
            profilePictInput.click();
        });

        profilePictInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (e) {
                cropPreview.src = e.target.result;

                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(cropPreview, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                });
            };

            reader.readAsDataURL(file);
        });

        cancelCropBtn.addEventListener('click', function () {
            cropModal.classList.add('hidden');
            cropModal.classList.remove('flex');

            profilePictInput.value = '';

            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        saveCropBtn.addEventListener('click', function () {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingQuality: 'high',
            });

            croppedImageInput.value = canvas.toDataURL('image/png');

            profilePhotoForm.submit();
        });
    }
});