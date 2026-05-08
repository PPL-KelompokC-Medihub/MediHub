const homeRoot = document.querySelector('[data-patient-home]');

if (homeRoot) {
    const doctorsBySpecialist = JSON.parse(homeRoot.dataset.doctors || '[]');
    let selectedDoctorIndex = null;

    const normalizeText = (text) => String(text || '').toLowerCase().replace('&', 'dan').trim();

    const openPoliPanel = (poliName) => {
        document.getElementById('poliTitle').innerText = poliName;

        const dokterJagaList = document.getElementById('dokterJagaList');
        dokterJagaList.innerHTML = '';
        selectedDoctorIndex = null;

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
                        class="js-select-doctor doctor-card relative mt-4 mb-3 w-full overflow-hidden rounded-2xl border-2 border-transparent bg-[linear-gradient(135deg,#D8EDFA_0%,#E5F5FF_45%,#C2EBFF_100%)] text-left shadow-md transition-all hover:shadow-lg">
                        
                        <div id="doctorCheck-${index}" class="doctor-check absolute right-3 top-3 z-30 hidden h-7 w-7 items-center justify-center rounded-full border-2 border-[#0077C2] bg-blue-100 text-[#0077C2]">
                            <i class="fa-solid fa-check text-sm"></i>
                        </div>

                        <div class="flex items-center gap-4 p-4">
                            <div class="h-[90px] w-[90px] flex-shrink-0 overflow-hidden rounded-xl bg-white/50">
                                ${
                                    doctor.foto
                                        ? `<img src="${doctor.foto}" alt="${doctor.nama}" class="h-full w-full object-cover object-top">`
                                        : `<div class="flex h-full w-full items-center justify-center text-blue-300">
                                            <i class="fa-solid fa-user-doctor text-4xl"></i>
                                        </div>`
                                }
                            </div>

                            <div class="flex-1 min-w-0">
                                <h2 class="text-[15px] font-semibold leading-snug text-[#1a1e26]">
                                    dr. ${String(doctor.nama || '').replace('dr. ', '')}
                                </h2>
                                <p class="mt-1 text-xs text-gray-500">${doctor.spesialis || ''}</p>
                                <p class="mt-3 text-xs font-semibold text-[#0077C2]">${doctor.pasien || ''} Terlayani</p>
                            </div>
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

        document.getElementById('lihatDetailButton')?.addEventListener('click', () => {
            if (selectedDoctorIndex === null) return;
            const doctor = filteredDoctors[selectedDoctorIndex];
            if (!doctor?.id) return;
            window.location.href = `/pasien/booking?doctor_id=${doctor.id}`;
        });

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

    // Deselect semua
    document.querySelectorAll('.doctor-card').forEach((card, i) => {
        card.classList.remove('border-[#0077C2]');
        card.classList.add('border-transparent');
        const check = document.getElementById(`doctorCheck-${i}`);
        if (check) {
            check.classList.add('hidden');
            check.classList.remove('flex', 'bg-[#0077C2]', 'border-[#0077C2]');
            check.classList.add('bg-white', 'border-gray-300');
        }
    });

    if (selectedDoctorIndex === index) {
        selectedDoctorIndex = null;
        detailButton.classList.remove('bg-blue-400', 'text-white');
        detailButton.classList.add('bg-gray-200', 'text-gray-500');
        return;
    }

    selectedDoctorIndex = index;

    const selectedCard = document.getElementById(`doctorCard-${index}`);
    selectedCard?.classList.remove('border-transparent');
    selectedCard?.classList.add('border-[#0077C2]');

    const check = document.getElementById(`doctorCheck-${index}`);
    if (check) {
        check.classList.remove('hidden', 'bg-white', 'border-gray-300');
        check.classList.add('flex', 'bg-[#0077C2]', 'border-[#0077C2]');
    }

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
        if (doctorButton) {
            selectDoctor(Number.parseInt(doctorButton.dataset.doctorIndex, 10));
            return;
        }

        const detailButton = event.target.closest('#lihatDetailButton');
        if (detailButton) {
            if (selectedDoctorIndex === null) return;
            const openPoli = document.getElementById('poliTitle')?.innerText || '';
            const filtered = doctorsBySpecialist.filter((doctor) => {
                const spesialis = normalizeText(doctor.spesialis || doctor.specialist || doctor.spesialisasi || doctor.category || doctor.poli || '');
                const poli = normalizeText(openPoli);
                return spesialis.includes(poli) || poli.includes(spesialis);
            });
            const doctor = filtered[selectedDoctorIndex];
            if (!doctor?.id) return;
            window.location.href = `/pasien/booking?doctor_id=${doctor.id}`;
        }
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
