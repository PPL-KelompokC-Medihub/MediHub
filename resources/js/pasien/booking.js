const bookingRoot = document.querySelector('[data-patient-booking]');

if (bookingRoot) {
    const schedules = JSON.parse(bookingRoot.dataset.schedules || '[]');
    const doctors = JSON.parse(bookingRoot.dataset.doctors || '[]');
    const selectedScheduleId = bookingRoot.dataset.selectedScheduleId || '';
    const selectedAppointmentTime = bookingRoot.dataset.selectedAppointmentTime || '';
    const doctorSelect = document.getElementById('doctor_id');
    const selectedDoctorSpecialization = document.getElementById('selectedDoctorSpecialization');
    const dateOptions = document.getElementById('dateOptions');
    const timeOptions = document.getElementById('timeOptions');
    const scheduleInput = document.getElementById('doctor_schedule_id');
    const appointmentTimeInput = document.getElementById('appointment_time');
    const emptyText = document.getElementById('schedule-empty-text');
    const rangeText = document.getElementById('scheduleRangeText');

    const formatDay = (dateString) => {
        const date = new Date(`${dateString}T00:00:00`);
        if (Number.isNaN(date.getTime())) return '-';

        return new Intl.DateTimeFormat('id-ID', { weekday: 'short' }).format(date);
    };

    const formatDate = (dateString) => {
        const date = new Date(`${dateString}T00:00:00`);
        if (Number.isNaN(date.getTime())) return { day: '--', weekday: '-' };

        return {
            day: new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date),
            weekday: formatDay(dateString),
        };
    };

    const parseMinutes = (time) => {
        const [hour, minute] = String(time || '').split(':').map((part) => Number.parseInt(part, 10));
        if (Number.isNaN(hour) || Number.isNaN(minute)) return null;

        return hour * 60 + minute;
    };

    const formatMinutes = (totalMinutes) => {
        const hour = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
        const minute = String(totalMinutes % 60).padStart(2, '0');

        return `${hour}:${minute}`;
    };

    const buildTimeSlots = (schedule) => {
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
    };

    const updateDoctorSummary = () => {
        const selectedDoctor = doctors.find((doctor) => String(doctor.id) === doctorSelect.value);
        selectedDoctorSpecialization.textContent = selectedDoctor
            ? `${selectedDoctor.specialization} | RS Medic Center - Bandung`
            : 'RS Medic Center - Bandung';
    };

    const selectSlot = (scheduleId, appointmentTime = null) => {
        scheduleInput.value = scheduleId;

        document.querySelectorAll('[data-schedule-id]').forEach((button) => {
            const active = button.dataset.scheduleId === String(scheduleId);
            button.classList.toggle('bg-blue-400', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-gray-500', !active);
        });

        if (appointmentTime) {
            appointmentTimeInput.value = appointmentTime;
        }

        document.querySelectorAll('[data-appointment-time]').forEach((button) => {
            const active = button.dataset.scheduleId === String(scheduleId) &&
                button.dataset.appointmentTime === appointmentTimeInput.value;

            button.classList.toggle('bg-blue-400', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-blue-400', active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-gray-900', !active);
            button.classList.toggle('border-gray-200', !active);
        });
    };

    const renderSchedules = () => {
        updateDoctorSummary();

        const doctorId = doctorSelect.value;
        const filtered = schedules.filter((schedule) => String(schedule.doctor_id) === doctorId);

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

        const initialSchedule = filtered.find((schedule) => String(schedule.id) === selectedScheduleId) || filtered[0];
        const initialTime = selectedAppointmentTime || buildTimeSlots(initialSchedule)[0] || '';
        selectSlot(initialSchedule.id, initialTime);
    };

    doctorSelect?.addEventListener('change', renderSchedules);
    renderSchedules();
}
