const bookingRoot = document.querySelector('[data-patient-booking]');

if (bookingRoot) {
    window.__medihubPatientBookingReady = false;

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
    const selectedDoctorId = bookingRoot.dataset.selectedDoctorId || '';
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
    const submitButton = document.querySelector('[data-booking-submit]');
    const medicalDocInput = document.getElementById('medical_doc');
    const medicalDocHelp = document.getElementById('medicalDocHelp');
    let activeSchedules = [];
    const maxMedicalDocSize = 2 * 1024 * 1024;

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
        if (
            Number.isNaN(hour) ||
            Number.isNaN(minute) ||
            hour < 0 ||
            hour > 23 ||
            minute < 0 ||
            minute > 59
        ) return null;

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

    const setSubmitState = () => {
        if (!submitButton) return;

        submitButton.disabled = !scheduleInput.value || !appointmentTimeInput.value;
        submitButton.classList.toggle('opacity-50', submitButton.disabled);
        submitButton.classList.toggle('cursor-not-allowed', submitButton.disabled);
    };

    const updateDoctorSummary = () => {
        if (!selectedDoctorSpecialization || !doctorSelect) return;

        const selectedDoctor = doctors.find((doctor) => String(doctor.id) === doctorSelect.value);
        selectedDoctorSpecialization.textContent = selectedDoctor
            ? `${selectedDoctor.specialization} | RS Medic Center - Bandung`
            : 'RS Medic Center - Bandung';
    };

    const renderTimeSlots = (scheduleId, selectedTime = null) => {
        const schedule = activeSchedules.find((item) => String(item.id) === String(scheduleId));
        timeOptions.innerHTML = '';

        if (!schedule) {
            appointmentTimeInput.value = '';
            setSubmitState();
            return;
        }

        const bookedTimes = new Set((schedule.booked_times || []).map((time) => String(time)));
        const slots = buildTimeSlots(schedule);
        const firstAvailable = slots.find((time) => !bookedTimes.has(time)) || '';
        const activeTime = selectedTime && !bookedTimes.has(selectedTime) ? selectedTime : firstAvailable;
        appointmentTimeInput.value = activeTime;

        slots.forEach((time) => {
            const isBooked = bookedTimes.has(time);
            const timeButton = document.createElement('button');
            timeButton.type = 'button';
            timeButton.dataset.scheduleId = schedule.id;
            timeButton.dataset.appointmentTime = time;
            timeButton.disabled = isBooked;
            timeButton.className = 'rounded-lg border px-4 py-2.5 text-sm transition';
            timeButton.textContent = isBooked ? `${time} penuh` : time;
            timeButton.addEventListener('click', () => selectSlot(schedule.id, time));
            timeOptions.appendChild(timeButton);
        });
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

        renderTimeSlots(scheduleId, appointmentTime);

        document.querySelectorAll('[data-appointment-time]').forEach((button) => {
            const active = button.dataset.scheduleId === String(scheduleId) &&
                button.dataset.appointmentTime === appointmentTimeInput.value;
            const disabled = button.disabled;

            button.classList.toggle('bg-blue-400', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-blue-400', active);
            button.classList.toggle('bg-white', !active && !disabled);
            button.classList.toggle('text-gray-900', !active && !disabled);
            button.classList.toggle('border-gray-200', !active && !disabled);
            button.classList.toggle('bg-gray-100', disabled);
            button.classList.toggle('text-gray-400', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        });

        setSubmitState();
    };

    const renderSchedules = () => {
        if (!doctorSelect || !dateOptions || !timeOptions || !scheduleInput || !appointmentTimeInput) {
            return;
        }

        if (!doctorSelect.value) {
            doctorSelect.value = selectedDoctorId || (doctors[0] ? String(doctors[0].id) : '');
        }

        updateDoctorSummary();

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

        activeSchedules = filtered;

        dateOptions.innerHTML = '';
        timeOptions.innerHTML = '';
        scheduleInput.value = '';
        appointmentTimeInput.value = '';
        setSubmitState();

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
                selectSlot(schedule.id);
            });
            dateOptions.appendChild(dateButton);
        });

        const initialSchedule = filtered.find((schedule) => String(schedule.id) === selectedScheduleId) || filtered[0];
        selectSlot(initialSchedule.id, selectedAppointmentTime || null);
    };

    doctorSelect?.addEventListener('change', renderSchedules);
    medicalDocInput?.addEventListener('change', () => {
        const file = medicalDocInput.files?.[0] || null;

        if (!file || !medicalDocHelp) {
            return;
        }

        const isTooLarge = file.size > maxMedicalDocSize;
        medicalDocHelp.textContent = isTooLarge
            ? 'File terlalu besar. Maksimal 2 MB.'
            : 'Max 2 MB. Format PDF, PNG, JPG, atau JPEG.';
        medicalDocHelp.classList.toggle('text-red-500', isTooLarge);
        medicalDocHelp.classList.toggle('text-gray-400', !isTooLarge);

        if (isTooLarge) {
            medicalDocInput.value = '';
        }
    });

    try {
        renderSchedules();
        window.__medihubPatientBookingReady = true;
    } catch (error) {
        console.error('Gagal menampilkan jadwal booking pasien.', error);
    }
}
