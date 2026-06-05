<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal Temu - MediHub</title>

    @vite(['resources/css/app.css', 'resources/js/pasien/booking.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-white font-[Poppins] text-[#111827]">
    <div
        data-patient-booking
        data-selected-doctor-id="{{ old('doctor_id', $selectedDoctorId) }}"
        data-selected-schedule-id="{{ old('doctor_schedule_id') }}"
        data-selected-appointment-time="{{ old('appointment_time') }}"
    ></div>
    <script type="application/json" id="patientBookingSchedules">
        @json($schedules)
    </script>
    <script type="application/json" id="patientBookingDoctors">
        @json($doctors)
    </script>
            <x-pasien.sidebar active="beranda" />
            <div class="ml-[220px] grid min-h-screen grid-cols-[1fr_390px]">

                <main class="bg-[#fbfbfb] px-6 py-8">
                    <div class="mx-auto max-w-[760px]">
                        @if ($errors->any())
                            <div class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('pasien.booking.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <header class="mb-6 flex items-center gap-5">
                                <a href="{{ route('pasien.beranda') }}" class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-600">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>

                                <div>
                                    <select id="doctor_id" name="doctor_id" required class="mb-1 bg-transparent text-lg font-semibold outline-none">
                                        <option value="">Pilih Dokter</option>
                                        @foreach ($doctors as $doctor)
                                            <option value="{{ $doctor['id'] }}" @selected((string) old('doctor_id', $selectedDoctorId) === (string) $doctor['id'])>
                                                {{ $doctor['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="selectedDoctorSpecialization" class="text-sm text-gray-500">RS Medic Center - Bandung</p>
                                </div>
                            </header>

                            <section class="mb-6 overflow-hidden rounded-xl">
                                <img src="{{ asset('images/clara.png') }}" alt="dr. Clara Wulandari" class="aspect-[755/254] w-full object-contain">
                            </section>

                            <section class="mb-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-600">Jadwal Tersedia</h2>
                                    <p id="scheduleRangeText" class="text-sm text-gray-500">Pilih dokter terlebih dahulu</p>
                                </div>

                                <div id="dateOptions" class="flex gap-2 overflow-x-auto pb-2"></div>
                                <input type="hidden" id="doctor_schedule_id" name="doctor_schedule_id" value="{{ old('doctor_schedule_id') }}">
                                <input type="hidden" id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}">
                                <p id="schedule-empty-text" class="mt-2 hidden text-xs text-red-500">Jadwal dokter tidak tersedia.</p>
                            </section>

                            <section class="mb-6">
                                <h2 class="mb-4 text-lg font-semibold">Waktu Tersedia</h2>
                                <div id="timeOptions" class="grid grid-cols-8 gap-2"></div>
                            </section>

                            <section class="mb-7">
                                <div class="mb-4 flex items-start justify-between">
                                    <div>
                                        <h2 class="mb-5 text-lg font-semibold">Informasi Pasien</h2>
                                        <p class="text-sm">Daftarkan saya sebagai pasien</p>
                                        <p class="text-xs text-gray-500">Data pasien akan otomatis terisi sesuai dengan profil anda</p>
                                    </div>

                                    <span class="mt-10 h-5 w-5 rounded-full border border-gray-300"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Nama Pasien</label>
                                        <input name="patient_name" value="{{ old('patient_name', $patient['fullname']) }}" required placeholder="John Doe" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none placeholder:text-gray-300">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Umur Pasien</label>
                                        <div class="flex items-center rounded-xl border border-gray-200 px-4 py-3">
                                            <input name="patient_age" type="number" value="{{ old('patient_age', $patient['umur']) }}" placeholder="12" class="w-full text-sm outline-none placeholder:text-gray-300">
                                            <span class="text-sm text-gray-300">Tahun</span>
                                        </div>
                                    </div>

                                    <input name="patient_email" type="hidden" value="{{ old('patient_email', $patient['email']) }}">

                                    <div class="col-span-2">
                                        <label class="mb-2 block text-sm font-medium">Jenis Kelamin</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-4 text-gray-500">
                                                <input type="radio" name="patient_gender" value="Perempuan" class="sr-only" @checked(old('patient_gender', $patient['gender']) === 'Perempuan')>
                                                <span class="text-xl">♀</span>
                                                <span class="text-sm">Perempuan</span>
                                            </label>

                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-4 text-gray-500">
                                                <input type="radio" name="patient_gender" value="Pria" class="sr-only" @checked(old('patient_gender', $patient['gender']) === 'Pria')>
                                                <span class="text-xl">♂</span>
                                                <span class="text-sm">Pria</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Berat Badan</label>
                                        <input name="patient_weight" type="number" step="0.1" value="{{ old('patient_weight', $patient['weight']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Tinggi Badan</label>
                                        <input name="patient_height" type="number" step="0.1" value="{{ old('patient_height', $patient['height']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Golongan Darah</label>
                                        <input name="blood_type" value="{{ old('blood_type', $patient['blood_type']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Dokumen Medis</label>
                                        <input id="medical_doc" name="medical_doc" type="file" accept=".pdf,.png,.jpg,.jpeg" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none">
                                        <p id="medicalDocHelp" class="mt-1 text-xs text-gray-400">Max 2 MB. Format PDF, PNG, JPG, atau JPEG.</p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Riwayat Alergi Obat</label>
                                        <textarea name="allergy_history" class="h-24 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">{{ old('allergy_history', $patient['allergy_history']) }}</textarea>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Keluhan / Gejala</label>
                                        <textarea name="complaint" required placeholder="Contoh: demam 2 hari, batuk, pusing..." class="h-24 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none">{{ old('complaint') }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button type="submit" data-booking-submit class="rounded-xl bg-blue-500 px-7 py-3 text-sm font-medium text-white shadow-md">
                                        Buat Jadwal Temu
                                        <i class="fa-solid fa-plus ml-2"></i>
                                    </button>
                                </div>
                            </section>
                        </form>
                    </div>
                </main>

                <aside class="sticky top-0 h-screen overflow-hidden border-l border-gray-200 bg-white px-7 py-8">
                    <div class="flex h-full min-h-0 flex-col">
                        <div class="mb-6 shrink-0">
                            <h2 class="text-lg font-semibold">Ulasan Pasien</h2>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                            @forelse ($reviews as $review)
                                <article class="mb-6 border-b border-gray-100 pb-6 last:mb-0 last:border-b-0">
                                    <div class="mb-3 flex items-start gap-3">
                                        <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover ring-2 ring-gray-50">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-sm font-medium">{{ $review['name'] }}</h3>
                                            <p class="mt-1 text-sm text-gray-500"><i class="fa-solid fa-star text-yellow-400"></i> {{ $review['rating'] }}</p>
                                        </div>
                                    </div>

                                    <p class="mb-4 text-justify text-sm leading-snug text-gray-900">{{ $review['text'] }}</p>

                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $review['date'] }}</span>
                                        <span><i class="fa-regular fa-heart mr-1"></i>{{ $review['likes'] }}</span>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-8 text-center">
                                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                                        <i class="fa-regular fa-comment-dots"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700">Belum ada ulasan</p>
                                    <p class="mt-1 text-xs leading-relaxed text-gray-400">
                                        Ulasan akan muncul setelah pasien mengirimkan pengalaman layanan.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        <div class="shrink-0 bg-white pt-5">
                            <a href="{{ route('pasien.layanan') }}" class="block w-full rounded-xl bg-blue-400 px-5 py-4 text-center text-sm font-medium text-white shadow-md transition hover:bg-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                Buat Ulasan
                            </a>
                        </div>
                    </div>
                </aside>
            </div>

    <script>
        window.setTimeout(() => {
            if (window.__medihubPatientBookingReady) {
                return;
            }

            const root = document.querySelector('[data-patient-booking]');
            const doctorSelect = document.getElementById('doctor_id');
            const dateOptions = document.getElementById('dateOptions');
            const timeOptions = document.getElementById('timeOptions');
            const scheduleInput = document.getElementById('doctor_schedule_id');
            const appointmentTimeInput = document.getElementById('appointment_time');
            const rangeText = document.getElementById('scheduleRangeText');
            const emptyText = document.getElementById('schedule-empty-text');
            const submitButton = document.querySelector('[data-booking-submit]');

            if (!root || !doctorSelect || !dateOptions || !timeOptions || !scheduleInput || !appointmentTimeInput) {
                return;
            }

            const parseJsonScript = (id) => {
                try {
                    const node = document.getElementById(id);
                    const parsed = JSON.parse(node ? node.textContent : '[]');

                    return Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    console.error('Gagal membaca data booking pasien.', error);

                    return [];
                }
            };

            const schedules = parseJsonScript('patientBookingSchedules');
            const doctors = parseJsonScript('patientBookingDoctors');

            if (!doctorSelect.value) {
                doctorSelect.value = root.dataset.selectedDoctorId || (doctors[0] ? String(doctors[0].id) : '');
            }

            const minutes = (time) => {
                const [hour, minute] = String(time || '').split(':').map((part) => Number.parseInt(part, 10));
                return Number.isNaN(hour) || Number.isNaN(minute) ? null : (hour * 60) + minute;
            };

            const formatMinutes = (value) => {
                const hour = String(Math.floor(value / 60)).padStart(2, '0');
                const minute = String(value % 60).padStart(2, '0');
                return `${hour}:${minute}`;
            };

            const slotsFor = (schedule) => {
                const start = minutes(schedule.start);
                const end = minutes(schedule.end);
                if (start === null || end === null || start >= end) {
                    return [schedule.start].filter(Boolean);
                }

                const slots = [];
                for (let current = start; current < end; current += 30) {
                    slots.push(formatMinutes(current));
                }
                return slots;
            };

            const setSubmitState = () => {
                if (!submitButton) {
                    return;
                }
                submitButton.disabled = !scheduleInput.value || !appointmentTimeInput.value;
                submitButton.classList.toggle('opacity-50', submitButton.disabled);
                submitButton.classList.toggle('cursor-not-allowed', submitButton.disabled);
            };

            const selectSchedule = (schedule) => {
                scheduleInput.value = schedule.id;
                timeOptions.innerHTML = '';

                const booked = new Set((schedule.booked_times || []).map(String));
                const slots = slotsFor(schedule);
                const firstAvailable = slots.find((slot) => !booked.has(slot)) || '';
                appointmentTimeInput.value = firstAvailable;

                slots.forEach((slot) => {
                    const button = document.createElement('button');
                    const isBooked = booked.has(slot);
                    button.type = 'button';
                    button.disabled = isBooked;
                    button.className = isBooked
                        ? 'rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-400'
                        : 'rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900';
                    button.textContent = isBooked ? `${slot} penuh` : slot;
                    button.addEventListener('click', () => {
                        appointmentTimeInput.value = slot;
                        setSubmitState();
                    });
                    timeOptions.appendChild(button);
                });

                setSubmitState();
            };

            const render = () => {
                const doctorId = doctorSelect.value;
                const selectedDoctorName = doctorSelect.options[doctorSelect.selectedIndex]?.textContent?.trim() || '';
                let filtered = schedules.filter((schedule) => {
                    return String(schedule.doctor_id) === doctorId ||
                        String(schedule.doctor_user_id || '') === doctorId;
                });

                if (filtered.length === 0 && selectedDoctorName !== '') {
                    filtered = schedules.filter((schedule) => {
                        return String(schedule.doctor_name || '').trim() === selectedDoctorName;
                    });
                }

                dateOptions.innerHTML = '';
                timeOptions.innerHTML = '';
                scheduleInput.value = '';
                appointmentTimeInput.value = '';

                if (!doctorId) {
                    if (rangeText) rangeText.textContent = 'Pilih dokter terlebih dahulu';
                    if (emptyText) emptyText.classList.add('hidden');
                    setSubmitState();
                    return;
                }

                if (filtered.length === 0) {
                    if (rangeText) rangeText.textContent = 'Jadwal tidak tersedia';
                    if (emptyText) emptyText.classList.remove('hidden');
                    setSubmitState();
                    return;
                }

                if (rangeText) rangeText.textContent = `${filtered[0].date || '-'} - ${filtered[filtered.length - 1].date || '-'}`;
                if (emptyText) emptyText.classList.add('hidden');

                filtered.forEach((schedule) => {
                    const date = new Date(`${schedule.date}T00:00:00`);
                    const day = Number.isNaN(date.getTime())
                        ? '--'
                        : new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date);
                    const weekday = Number.isNaN(date.getTime())
                        ? '-'
                        : new Intl.DateTimeFormat('id-ID', { weekday: 'short' }).format(date);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'min-w-[86px] rounded-xl bg-white px-5 py-4 text-center text-gray-500 shadow-sm transition';
                    button.innerHTML = `<p class="text-2xl font-semibold">${day}</p><p class="text-sm">${weekday}</p>`;
                    button.addEventListener('click', () => selectSchedule(schedule));
                    dateOptions.appendChild(button);
                });

                selectSchedule(filtered[0]);
            };

            doctorSelect.addEventListener('change', render);
            render();
        }, 300);
    </script>
</body>
</html>
