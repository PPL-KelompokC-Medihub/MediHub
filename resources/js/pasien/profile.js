document.addEventListener('DOMContentLoaded', () => {
    const sections = [
        {
            section: document.querySelector('[data-edit-section="profile"]'),
            editButton: document.getElementById('editProfileBtn'),
            cancelButton: document.getElementById('cancelProfileBtn'),
            inputs: Array.from(document.querySelectorAll('.profile-input')),
        },
        {
            section: document.querySelector('[data-edit-section="address"]'),
            editButton: document.getElementById('editAddressBtn'),
            cancelButton: document.getElementById('cancelAddressBtn'),
            inputs: Array.from(document.querySelectorAll('.address-input')),
        },
    ];

    sections.forEach(initEditableSection);
    initAllergyToggle();
    initProfileCompletion();
    initModalTriggers();
    initPhotoCrop();
});

function initEditableSection(config) {
    const { section, editButton, cancelButton, inputs } = config;

    if (!section || !editButton || !cancelButton || inputs.length === 0) {
        return;
    }

    const saveWrapper = section.querySelector('[data-save-wrapper]');
    const saveButton = section.querySelector('[data-save-button]');
    const status = section.querySelector('[data-edit-status]');
    const unsavedMessage = section.querySelector('[data-unsaved-message]');
    const initialState = snapshotInputs(inputs);

    setSectionEditing(config, false, initialState);

    editButton.addEventListener('click', () => {
        setSectionEditing(config, true, initialState);
        focusFirstEditableInput(inputs);
    });

    cancelButton.addEventListener('click', () => {
        if (isDirty(inputs, initialState) && !window.confirm('Batalkan perubahan yang belum disimpan?')) {
            return;
        }

        restoreInputs(inputs, initialState);
        setSectionEditing(config, false, initialState);
        updateProfileCompletion();
    });

    inputs.forEach((input) => {
        input.addEventListener('input', () => updateDirtyState(inputs, initialState, saveButton, unsavedMessage));
        input.addEventListener('change', () => {
            updateDirtyState(inputs, initialState, saveButton, unsavedMessage);
            updateProfileCompletion();
        });
    });

    section.querySelector('form')?.addEventListener('submit', () => {
        saveButton?.setAttribute('disabled', 'disabled');
        if (saveButton) {
            saveButton.textContent = 'Menyimpan...';
        }
    });

    function setSectionEditing(currentConfig, isEditing, baseline) {
        currentConfig.inputs.forEach((input) => {
            if (isToggleInput(input)) {
                input.disabled = !isEditing;
            } else {
                input.readOnly = !isEditing;
            }
        });

        currentConfig.section.classList.toggle('border-blue-200', isEditing);
        currentConfig.section.classList.toggle('shadow-[0_18px_45px_rgba(59,130,246,0.08)]', isEditing);
        currentConfig.editButton.classList.toggle('hidden', isEditing);
        saveWrapper?.classList.toggle('hidden', !isEditing);
        saveWrapper?.classList.toggle('flex', isEditing);

        if (status) {
            status.textContent = isEditing
                ? 'Mode edit aktif. Ubah data lalu simpan.'
                : 'Mode lihat. Klik edit untuk mengubah data.';
            status.classList.toggle('text-blue-500', isEditing);
        }

        updateDirtyState(currentConfig.inputs, baseline, saveButton, unsavedMessage);
    }
}

function initAllergyToggle() {
    const noAllergyInput = document.getElementById('noAllergyInput');
    const allergyHistoryInput = document.getElementById('allergyHistoryInput');

    if (!noAllergyInput || !allergyHistoryInput) {
        return;
    }

    const syncAllergyState = () => {
        if (!noAllergyInput.checked || noAllergyInput.disabled) {
            allergyHistoryInput.classList.remove('bg-blue-50');
            return;
        }

        allergyHistoryInput.value = '';
        allergyHistoryInput.placeholder = 'Tidak ada alergi obat';
        allergyHistoryInput.classList.add('bg-blue-50');
    };

    noAllergyInput.addEventListener('change', syncAllergyState);
    syncAllergyState();
}

function initProfileCompletion() {
    window.updateProfileCompletion = updateProfileCompletion;
    updateProfileCompletion();
}

function updateProfileCompletion() {
    const fields = Array.from(document.querySelectorAll('[data-profile-completion]'));
    const text = document.getElementById('profileCompletionText');
    const bar = document.getElementById('profileCompletionBar');
    const hint = document.getElementById('profileCompletionHint');

    if (fields.length === 0 || !text || !bar) {
        return;
    }

    const names = [...new Set(fields.map((field) => field.name).filter(Boolean))];
    const filled = names.filter((name) => {
        const group = fields.filter((field) => field.name === name);
        return group.some((field) => isToggleInput(field) ? field.checked : field.value.trim() !== '');
    }).length;
    const percent = Math.round((filled / names.length) * 100);

    text.textContent = `${percent}%`;
    bar.style.width = `${percent}%`;

    if (hint) {
        hint.textContent = percent >= 100
            ? 'Profil sudah lengkap untuk booking dan konsultasi.'
            : 'Lengkapi data agar booking dan konsultasi lebih cepat.';
    }
}

function initModalTriggers() {
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.modalOpen));
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.modalClose));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('.fixed.inset-0.z-50').forEach((modal) => {
            if (!modal.classList.contains('hidden')) {
                closeModal(modal.id);
            }
        });
    });
}

function initPhotoCrop() {
    const choosePhotoBtn = document.getElementById('choosePhotoBtn');
    const profilePictInput = document.getElementById('profilePictInput');
    const cropModal = document.getElementById('cropModal');
    const cropPreview = document.getElementById('cropPreview');
    const cancelCropBtn = document.getElementById('cancelCropBtn');
    const saveCropBtn = document.getElementById('saveCropBtn');
    const croppedImageInput = document.getElementById('croppedImageInput');
    const profilePhotoForm = document.getElementById('profilePhotoForm');
    const photoPreview = document.querySelector('[data-profile-photo-preview]');

    if (!choosePhotoBtn || !profilePictInput || !cropModal || !cropPreview || !cancelCropBtn || !saveCropBtn || !profilePhotoForm) {
        return;
    }

    let cropper = null;

    choosePhotoBtn.addEventListener('click', () => profilePictInput.click());

    profilePictInput.addEventListener('change', (event) => {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            window.alert('Pilih file gambar yang valid.');
            profilePictInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (readerEvent) => {
            cropPreview.src = readerEvent.target?.result || '';
            photoPreview?.setAttribute('src', cropPreview.src);
            openModal('cropModal');

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropPreview, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                responsive: true,
                background: false,
            });
        };
        reader.readAsDataURL(file);
    });

    cancelCropBtn.addEventListener('click', () => {
        closeModal('cropModal');
        profilePictInput.value = '';

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    saveCropBtn.addEventListener('click', () => {
        if (!cropper) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingQuality: 'high',
        });

        if (croppedImageInput) {
            croppedImageInput.value = canvas.toDataURL('image/png');
        }

        saveCropBtn.disabled = true;
        saveCropBtn.textContent = 'Mengunggah...';
        profilePhotoForm.submit();
    });
}

function snapshotInputs(inputs) {
    return inputs.map((input) => ({
        input,
        value: input.value,
        checked: input.checked,
    }));
}

function restoreInputs(inputs, snapshot) {
    snapshot.forEach(({ input, value, checked }) => {
        if (!inputs.includes(input)) {
            return;
        }

        input.value = value;
        input.checked = checked;
    });
}

function isDirty(inputs, snapshot) {
    return snapshot.some(({ input, value, checked }) => (
        inputs.includes(input) && (input.value !== value || input.checked !== checked)
    ));
}

function updateDirtyState(inputs, snapshot, saveButton, unsavedMessage) {
    const dirty = isDirty(inputs, snapshot);

    if (saveButton) {
        saveButton.disabled = !dirty;
    }

    unsavedMessage?.classList.toggle('hidden', !dirty);
}

function focusFirstEditableInput(inputs) {
    const firstInput = inputs.find((input) => !isToggleInput(input));
    firstInput?.focus();
}

function isToggleInput(input) {
    return input.type === 'radio' || input.type === 'checkbox';
}

function openModal(id) {
    const modal = id ? document.getElementById(id) : null;

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(id) {
    const modal = id ? document.getElementById(id) : null;

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
