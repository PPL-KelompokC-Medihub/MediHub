<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal Temu - MediHub</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-white font-[Poppins] text-[#111827]">
    <div class="grid min-h-screen grid-cols-[220px_1fr_390px] overflow-hidden">
        <aside class="flex flex-col justify-between border-r border-gray-200 px-7 py-8">
            <div>
                <img src="{{ asset('images/Medihub.png') }}" alt="Logo MediHub" class="mb-10 h-14 w-auto object-contain">
                <p class="mb-6 text-lg font-semibold">Menu</p>

                <nav class="flex flex-col gap-7 text-[15px]">
                    <a href="{{ route('pasien.beranda') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-solid fa-house"></i> Beranda
                    </a>
                    <a href="{{ route('pasien.layanan') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-solid fa-bed-pulse"></i> Layanan
                    </a>
                    <a href="#" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-clock"></i> Riwayat
                    </a>
                    <a href="{{ route('pasien.profile') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-user"></i> Profil
                    </a>
                </nav>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left text-sm text-gray-500">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Keluar
                </button>
            </form>
        </aside>

        <main class="overflow-y-auto bg-[#fbfbfb] px-6 py-8">
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
                                <option value="{{ $doctor['id'] }}" @selected(old('doctor_id', $selectedDoctorId) === $doctor['id'])>
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
                            <input name="medical_doc" type="file" accept=".pdf,.png,.jpg,.jpeg" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none">
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
                        <button type="submit" class="rounded-xl bg-blue-500 px-7 py-3 text-sm font-medium text-white shadow-md">
                            Buat Jadwal Temu
                            <i class="fa-solid fa-plus ml-2"></i>
                        </button>
                    </div>
                </section>
            </form>
        </main>

        <aside class="flex h-screen flex-col border-l border-gray-200 bg-white px-7 py-10">
            <h2 class="mb-6 text-lg font-semibold">Ulasan Pasien</h2>

            <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                @foreach ([
                    ['name' => 'Rina', 'rating' => '5.0', 'text' => 'Pelayanan cepat dan terorganisir. Saya tidak perlu menunggu lama di ruang tunggu karena antrian sudah bisa daftar lewat aplikasi.', 'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=120&auto=format&fit=crop'],
                    ['name' => 'Melati', 'rating' => '4.0', 'text' => 'IGD buka 24 jam dan respon perawatnya sigap sekali. Hanya saja area parkir agak penuh di jam sibuk.', 'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=120&auto=format&fit=crop'],
                    ['name' => 'Ahmad', 'rating' => '4.0', 'text' => 'Secara keseluruhan puas, apalagi dengan adanya sistem antrian online jadi lebih efisien.', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=120&auto=format&fit=crop'],
                    ['name' => 'Yanto', 'rating' => '5.0', 'text' => 'Anak saya dirawat di ruang anak, suasananya dibuat ceria dan ramah anak.', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=120&auto=format&fit=crop'],
                    ['name' => 'Sergey', 'rating' => '5.0', 'text' => 'Dokternya ramah dan menjelaskan kondisi saya dengan jelas.', 'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=120&auto=format&fit=crop'],
                ] as $review)
                    <article class="mb-5 border-b border-gray-100 pb-5">
                        <div class="mb-3 flex items-start gap-3">
                            <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover">
                            <div>
                                <h3 class="text-sm font-medium">{{ $review['name'] }}</h3>
                                <p class="text-sm text-gray-500"><i class="fa-solid fa-star text-yellow-400"></i> {{ $review['rating'] }}</p>
                            </div>
                        </div>

                        <p class="mb-4 text-sm leading-snug text-gray-900">{{ $review['text'] }}</p>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>17 Agustus | 19:07 PM</span>
                            <span><i class="fa-regular fa-heart mr-1"></i>5</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <button class="mt-5 rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium text-white shadow-md">
                Buat Ulasan
            </button>
        </aside>
    </div>

    <script>
        const schedules = @json($schedules);
        const doctors = @json($doctors);
        const selectedScheduleId = @json(old('doctor_schedule_id'));
        const selectedAppointmentTime = @json(old('appointment_time'));
        const doctorSelect = document.getElementById('doctor_id');
        const selectedDoctorSpecialization = document.getElementById('selectedDoctorSpecialization');
        const dateOptions = document.getElementById('dateOptions');
        const timeOptions = document.getElementById('timeOptions');
        const scheduleInput = document.getElementById('doctor_schedule_id');
        const appointmentTimeInput = document.getElementById('appointment_time');
        const emptyText = document.getElementById('schedule-empty-text');
        const rangeText = document.getElementById('scheduleRangeText');
        function formatDay(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            if (Number.isNaN(date.getTime())) return '-';

            return new Intl.DateTimeFormat('id-ID', { weekday: 'short' }).format(date);
        }

        function formatDate(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            if (Number.isNaN(date.getTime())) return { day: '--', weekday: '-' };

            return {
                day: new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date),
                weekday: formatDay(dateString),
            };
        }

        function parseMinutes(time) {
            const [hour, minute] = String(time || '').split(':').map((part) => Number.parseInt(part, 10));

            if (Number.isNaN(hour) || Number.isNaN(minute)) return null;

            return hour * 60 + minute;
        }

        function formatMinutes(totalMinutes) {
            const hour = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
            const minute = String(totalMinutes % 60).padStart(2, '0');

            return `${hour}:${minute}`;
        }

        function buildTimeSlots(schedule) {
            const start = parseMinutes(schedule.start);
            const end = parseMinutes(schedule.end);

            if (start === null || end === null || start >= end) {
                return [schedule.start].filter(Boolean);
            }

            const slots = [];
            for (let current = start; current < end; current += 30) {
                slots.push(formatMinutes(current));
            }

            return slots;
        }

        function updateDoctorSummary() {
            const selectedDoctor = doctors.find((doctor) => doctor.id === doctorSelect.value);
            selectedDoctorSpecialization.textContent = selectedDoctor
                ? `${selectedDoctor.specialization} | RS Medic Center - Bandung`
                : 'RS Medic Center - Bandung';
        }

        function selectSlot(scheduleId, appointmentTime = null) {
            scheduleInput.value = scheduleId;
            document.querySelectorAll('[data-schedule-id]').forEach((button) => {
                const active = button.dataset.scheduleId === scheduleId;
                button.classList.toggle('bg-blue-400', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('text-gray-500', !active);
            });

            if (appointmentTime) {
                appointmentTimeInput.value = appointmentTime;
            }

            document.querySelectorAll('[data-appointment-time]').forEach((button) => {
                const active = button.dataset.scheduleId === scheduleId &&
                    button.dataset.appointmentTime === appointmentTimeInput.value;

                button.classList.toggle('bg-blue-400', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('border-blue-400', active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('text-gray-900', !active);
                button.classList.toggle('border-gray-200', !active);
            });
        }

        function renderSchedules() {
            updateDoctorSummary();

            const doctorId = doctorSelect.value;
            const filtered = schedules.filter((schedule) => schedule.doctor_id === doctorId);

            dateOptions.innerHTML = '';
            timeOptions.innerHTML = '';
            scheduleInput.value = '';
            appointmentTimeInput.value = '';

            if (!doctorId) {
                emptyText.classList.add('hidden');
                rangeText.textContent = 'Pilih dokter terlebih dahulu';
                return;
            }

            if (filtered.length === 0) {
                emptyText.classList.remove('hidden');
                rangeText.textContent = 'Jadwal tidak tersedia';
                return;
            }

            emptyText.classList.add('hidden');
            rangeText.textContent = `${filtered[0].date || '-'} - ${filtered[filtered.length - 1].date || '-'}`;

            filtered.forEach((schedule) => {
                const date = formatDate(schedule.date);
                const dateButton = document.createElement('button');
                dateButton.type = 'button';
                dateButton.dataset.scheduleId = schedule.id;
                dateButton.className = 'min-w-[86px] rounded-xl bg-white px-5 py-4 text-center text-gray-500 shadow-sm transition';
                dateButton.innerHTML = `<p class="text-2xl font-semibold">${date.day}</p><p class="text-sm">${date.weekday}</p>`;
                dateButton.addEventListener('click', () => {
                    const firstSlot = buildTimeSlots(schedule)[0] || '';
                    selectSlot(schedule.id, firstSlot);
                });
                dateOptions.appendChild(dateButton);

                buildTimeSlots(schedule).forEach((time) => {
                    const timeButton = document.createElement('button');
                    timeButton.type = 'button';
                    timeButton.dataset.scheduleId = schedule.id;
                    timeButton.dataset.appointmentTime = time;
                    timeButton.className = 'rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm transition';
                    timeButton.textContent = time;
                    timeButton.addEventListener('click', () => selectSlot(schedule.id, time));
                    timeOptions.appendChild(timeButton);
                });
            });

            const initialSchedule = filtered.find((schedule) => schedule.id === selectedScheduleId) || filtered[0];
            const initialTime = selectedAppointmentTime || buildTimeSlots(initialSchedule)[0] || '';
            selectSlot(initialSchedule.id, initialTime);
        }

        doctorSelect.addEventListener('change', renderSchedules);
        renderSchedules();
    </script>
</body>
</html>
