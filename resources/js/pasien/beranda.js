const homeRoot = document.querySelector('[data-patient-home]');

if (homeRoot) {
    const doctorsBySpecialist = JSON.parse(homeRoot.dataset.doctors || '[]');
    let selectedDoctorIndex = null;

    const normalizeText = (text) => String(text || '').toLowerCase().replace('&', 'dan').trim();

    const openPoliPanel = (poliName) => {
        document.getElementById('poliTitle').innerText = poliName;

        const dokterJagaList = document.getElementById('dokterJagaList');
        dokterJagaList.innerHTML = '';

        const selectedPoli = normalizeText(poliName);

        console.log('DATA DOCTORS:', doctorsBySpecialist);
        console.log('POLI DIPILIH:', selectedPoli);

        const filteredDoctors = doctorsBySpecialist.filter((doctor) => {
            const spesialis = normalizeText(
                doctor.spesialis ||
                doctor.specialist ||
                doctor.spesialisasi ||
                doctor.category ||
                doctor.poli ||
                ''
            );

            return spesialis.includes(selectedPoli) || selectedPoli.includes(spesialis);
        });

        console.log('HASIL FILTER:', filteredDoctors);

        if (filteredDoctors.length === 0) {
            dokterJagaList.innerHTML = `
                <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-10 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                        <i class="fa-solid fa-user-doctor text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Belum ada dokter jaga</p>
                    <p class="mt-1 text-xs leading-relaxed text-gray-400">Data dokter jaga untuk poli ini belum tersedia.</p>
                </div>
            `;
        } else {
            filteredDoctors.forEach((doctor, index) => {
                dokterJagaList.innerHTML += `
                    <button
                        type="button"
                        data-doctor-index="${index}"
                        id="doctorCard-${index}"
                        class="js-select-doctor doctor-card relative h-[230px] w-full overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#D8EDFA_0%,#E5F5FF_45%,#C2EBFF_100%)] text-left shadow-md">
                        <div id="doctorCheck-${index}" class="doctor-check absolute right-4 top-4 z-30 hidden flex h-7 w-7 items-center justify-center rounded-full border-2 border-[#0077C2] bg-blue-100 text-[#0077C2]">
                            <i class="fa-solid fa-check text-sm"></i>
                        </div>
                        <div class="absolute left-5 top-8 z-10 max-w-[150px]">
                            <h2 class="min-h-[92px] text-[34px] font-semibold uppercase leading-tight tracking-wide text-blue-300/70">
                                ${String(doctor.nama || '').replace('dr. ', '')}
                            </h2>
                            <p class="mt-15 bg-[linear-gradient(90deg,#0077C2_0%,#003556_100%)] bg-clip-text text-sm font-semibold text-transparent">
                                ${doctor.pasien || ''}
                            </p>
                            <p class="bg-[linear-gradient(90deg,#0077C2_0%,#003556_100%)] bg-clip-text text-xs text-transparent">
                                Terlayani
                            </p>
                        </div>
                        <div class="absolute bottom-0 right-0 z-20 flex h-full w-[58%] items-end justify-center">
                            ${
                                doctor.foto
                                    ? `<img src="${doctor.foto}" alt="${doctor.nama}" class="h-[210px] w-auto object-contain">`
                                    : `<div class="mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-white/50 text-blue-400">
                                        <i class="fa-solid fa-user-doctor text-5xl"></i>
                                    </div>`
                            }
                        </div>
                    </button>
                `;
            });

            dokterJagaList.innerHTML += `
                <button id="lihatDetailButton" class="mt-90 flex w-full items-center justify-between rounded-lg bg-gray-200 px-4 py-3 text-sm font-medium text-gray-500 shadow-md transition">
                    Lihat detail
                    <i class="fa-solid fa-plus"></i>
                </button>
            `;
        }

        document.getElementById('poliOverlay').classList.remove('hidden');
        document.getElementById('poliPanel').classList.remove('translate-x-full');
    };

    const closePoliPanel = () => {
        document.getElementById('poliPanel').classList.add('translate-x-full');

        setTimeout(() => {
            document.getElementById('poliOverlay').classList.add('hidden');
        }, 300);
    };

    const selectDoctor = (index) => {
        const detailButton = document.getElementById('lihatDetailButton');
        if (!detailButton) return;

        if (selectedDoctorIndex === index) {
            selectedDoctorIndex = null;
            document.getElementById(`doctorCheck-${index}`)?.classList.add('hidden');
            detailButton.classList.remove('bg-blue-400', 'text-white');
            detailButton.classList.add('bg-gray-200', 'text-gray-500');
            return;
        }

        selectedDoctorIndex = index;

        document.querySelectorAll('.doctor-check').forEach((check) => {
            check.classList.add('hidden');
        });

        document.getElementById(`doctorCheck-${index}`)?.classList.remove('hidden');
        detailButton.classList.remove('bg-gray-200', 'text-gray-500');
        detailButton.classList.add('bg-blue-400', 'text-white');
    };

    document.querySelectorAll('[data-poli-name]').forEach((button) => {
        button.addEventListener('click', () => openPoliPanel(button.dataset.poliName || ''));
    });

    document.querySelectorAll('[data-close-poli]').forEach((button) => {
        button.addEventListener('click', closePoliPanel);
    });

    document.getElementById('dokterJagaList')?.addEventListener('click', (event) => {
        const doctorButton = event.target.closest('.js-select-doctor');
        if (!doctorButton) return;

        selectDoctor(Number.parseInt(doctorButton.dataset.doctorIndex, 10));
    });

    const placeholders = [
        'Dokter Umum',
        'Cari dokter kandungan',
        'Konsultasi dokter',
        'Cari jadwal konsultasi',
    ];

    const input = document.getElementById('animatedSearch');
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    const typePlaceholder = () => {
        if (!input) return;

        const currentText = placeholders[textIndex];
        if (!isDeleting) {
            input.setAttribute('placeholder', currentText.substring(0, charIndex + 1));
            charIndex++;

            if (charIndex === currentText.length) {
                isDeleting = true;
                setTimeout(typePlaceholder, 1800);
                return;
            }
        } else {
            input.setAttribute('placeholder', currentText.substring(0, charIndex - 1));
            charIndex--;

            if (charIndex === 0) {
                isDeleting = false;
                textIndex = (textIndex + 1) % placeholders.length;
            }
        }

        setTimeout(typePlaceholder, isDeleting ? 35 : 70);
    };

    typePlaceholder();
}
