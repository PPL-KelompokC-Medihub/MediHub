<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan - MediHub</title>

    @vite(['resources/css/app.css', 'resources/js/pasien/layanan.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="overflow-x-hidden bg-white font-[Poppins] text-[#111827]">
    <x-pasien.sidebar active="layanan" />

    <div class="ml-[220px] grid h-screen grid-cols-[minmax(0,1fr)_390px] overflow-hidden bg-white">
        <main class="min-w-0 overflow-x-hidden h-screen overflow-y-auto bg-white px-8 py-10">
            <div class="mb-7 flex items-start justify-between gap-6">
                <div>
                    <p class="mb-1 text-[24px] font-semibold leading-tight text-[#6AA4EF]">{{ $hospital['type'] }}</p>
                    <h1 class="text-[36px] font-semibold leading-tight tracking-normal text-black">{{ $hospital['name'] }}</h1>
                </div>

                <button
                    type="button"
                    data-notification-open
                    aria-haspopup="dialog"
                    aria-controls="notificationCenter"
                    class="relative mt-1 flex h-[50px] w-[50px] items-center justify-center rounded-xl border border-gray-200 bg-white text-[22px] text-gray-500 shadow-[0_8px_20px_rgba(17,24,39,0.04)] transition hover:-translate-y-0.5 hover:text-[#58A7F7] hover:shadow-[0_12px_24px_rgba(17,24,39,0.08)] focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <i class="fa-regular fa-bell"></i>
                </button>
            </div>

            <section class="mb-8">
                <div class="mb-6 flex flex-wrap items-end gap-6">
                    <div>
                        <p class="mb-1 text-base font-normal text-[#888888]">Jam Operasional</p>
                        <p class="text-[24px] font-semibold leading-tight text-black">{{ $hospital['operational_hour'] }}</p>
                    </div>

                    <div class="h-[50px] w-px bg-gray-200"></div>

                    <div class="text-[28px] font-semibold leading-tight text-black">
                        IGD <span class="text-[#1E5B91]">{{ $hospital['emergency_hour'] }}</span>
                    </div>

                    <div class="ml-auto flex items-center gap-4 text-[28px] font-medium leading-tight text-black">
                        <i class="fa-solid fa-phone text-[26px] text-[#2f343c]"></i>
                        <span>{{ $hospital['phone'] }}</span>
                    </div>
                </div>

                <p class="mb-8 max-w-[920px] text-justify text-[15px] font-normal leading-[1.45] text-[#777777]">
                    <span class="font-semibold text-[#6f6f6f]">Rumah sakit umum</span> {{ $hospital['description'] }}
                    Dengan <span class="font-semibold text-[#6f6f6f]">fasilitas lengkap</span> {{ $hospital['description_support'] }}
                    {{ $hospital['vision'] }}
                </p>

                <div class="flex items-center justify-between gap-4">
                    <p class="flex items-center gap-3 text-[16px] font-medium text-black">
                        <i class="fa-solid fa-location-dot text-[20px] text-[#555555]"></i>
                        {{ $hospital['address'] }}
                    </p>

                    <button class="flex h-[50px] w-[50px] items-center justify-center rounded-xl border border-gray-200 bg-white text-[22px] text-gray-500 shadow-[0_8px_20px_rgba(17,24,39,0.04)]">
                        <i class="fa-solid fa-route"></i>
                    </button>
                </div>
            </section>

            <section class="mb-7">
                <h2 class="mb-5 text-[20px] font-semibold leading-tight text-black">Kategori Poli</h2>

                <div class="flex gap-7 overflow-x-auto pb-4">
                    @foreach ($categories as $category)
                        <div class="group min-w-[86px] text-center">
                            <div class="mx-auto mb-3 flex h-[78px] w-[78px] items-center justify-center rounded-full bg-[#6AA4EF] text-3xl text-white transition-transform duration-200 group-hover:scale-105">
                                <img src="{{ asset('images/categories/' . $category['icon']) }}" alt="{{ $category['nama'] }}" class="h-10 w-10 object-contain">
                            </div>

                            <p class="text-[16px] font-normal leading-tight text-black">{{ $category['nama'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="mb-8">
                <h2 class="mb-4 text-[20px] font-semibold leading-tight text-black">Dokter Pilihan Pasien</h2>

                <div class="flex gap-5 overflow-x-auto pb-4">
                    @foreach ($doctors as $doctor)
                        <a href="{{ $doctor['id'] ? route('pasien.booking.create', ['doctor_id' => $doctor['id']]) : route('pasien.booking.create') }}" class="min-w-[215px] overflow-hidden rounded-lg bg-white shadow-[0_12px_24px_rgba(17,24,39,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_30px_rgba(17,24,39,0.12)]">
                            <img src="{{ $doctor['foto'] }}" alt="{{ $doctor['nama'] }}" class="h-[155px] w-full bg-[#DDF0FF] object-cover">

                            <div class="p-4">
                                <h3 class="mb-1 min-h-[42px] text-[16px] font-semibold leading-[1.25] text-black">{{ $doctor['nama'] }}</h3>
                                <p class="mb-4 text-[14px] leading-snug text-[#858585]">{{ $doctor['spesialis'] }}</p>

                                <div class="flex items-center gap-3 text-[14px] text-[#858585]">
                                    <span><i class="fa-solid fa-star text-[#F3CC4E]"></i> {{ $doctor['rating'] }}</span>
                                    <span class="h-5 w-px bg-gray-200"></span>
                                    <span>{{ $doctor['pasien'] }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-[20px] font-semibold leading-tight text-black">Fasilitas Unggulan</h2>
                <ul class="grid grid-cols-2 gap-x-8 gap-y-2 text-[15px] text-[#858585]">
                    @foreach ($simpleFacilities as $facility)
                        <li class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                            {{ $facility }}
                        </li>
                    @endforeach
                </ul>
            </section>
        </main>

        <aside class="h-screen overflow-hidden border-l border-gray-200 bg-white px-7 py-10">
            <div class="flex h-full min-h-0 flex-col">
                <div class="mb-7 shrink-0">
                    <h2 class="text-[20px] font-semibold leading-tight text-black">
                        Ulasan Pasien
                    </h2>
                </div>

                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm text-green-700">
                        <i class="fa-regular fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600"
                        data-review-open-on-load
                    >
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                    @forelse ($reviews as $review)
                        <article class="mb-7 border-b border-gray-100 pb-7 last:mb-0 last:border-b-0">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover ring-2 ring-gray-50">

                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate text-[16px] font-medium leading-tight text-black">{{ $review['name'] }}</h3>
                                        <p class="mt-1 text-[15px] text-[#777777]">
                                            <i class="fa-solid fa-star text-[#F3CC4E]"></i>
                                            {{ $review['rating'] }}
                                        </p>
                                    </div>
                                </div>

                                @if (($review['patient_id'] ?? null) === (string) Auth::id())
                                    <div class="flex items-center gap-2 shrink-0">
                                        <!-- Edit button -->
                                        <button
                                            type="button"
                                            class="text-blue-500 hover:text-blue-700 transition"
                                            data-edit-review-btn
                                            data-id="{{ $review['id'] }}"
                                            data-rating="{{ $review['rating'] }}"
                                            data-text="{{ $review['text'] }}"
                                            title="Ubah Ulasan"
                                        >
                                            <i class="fa-regular fa-pen-to-square text-base"></i>
                                        </button>

                                        <!-- Hapus form -->
                                        <form
                                            method="POST"
                                            action="{{ route('pasien.layanan.ulasan.destroy', ['id' => $review['id']]) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Hapus Ulasan">
                                                <i class="fa-regular fa-trash-can text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            <p class="mb-5 text-justify text-[15px] leading-[1.35] text-black">{{ $review['text'] }}</p>

                            <div class="flex items-center justify-between text-[12px] text-[#858585]">
                                <span>{{ $review['date'] }}</span>
                                <span class="text-[14px]"><i class="fa-regular fa-heart mr-1"></i>{{ $review['likes'] }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-8 text-center">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                                <i class="fa-regular fa-comment-dots"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">Belum ada ulasan</p>
                            <p class="mt-1 text-xs leading-relaxed text-gray-400">
                                Jadilah pasien pertama yang memberi ulasan layanan.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="shrink-0 bg-white pt-5">
                    <button
                        type="button"
                        data-review-open
                        class="w-full rounded-xl bg-[#6AA4EF] px-5 py-4 text-[15px] font-medium text-white shadow-[0_10px_18px_rgba(106,164,239,0.32)] transition hover:bg-[#5597E8] focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        Buat Ulasan
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <div
        id="notificationOverlay"
        data-notification-close
        class="fixed inset-0 z-40 hidden bg-black/20"
    ></div>

    <section
        id="notificationCenter"
        role="dialog"
        aria-modal="true"
        aria-labelledby="notificationCenterTitle"
        class="fixed right-8 top-8 z-50 hidden h-[calc(100vh-64px)] w-[390px] max-w-[calc(100vw-32px)] translate-x-6 flex-col rounded-2xl border border-gray-100 bg-white shadow-[0_24px_60px_rgba(17,24,39,0.18)] opacity-0 transition duration-200"
    >
        <header class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.08em] text-[#58A7F7]">Notification Center</p>
                <h2 id="notificationCenterTitle" class="mt-1 text-xl font-semibold text-black">Pusat Notifikasi</h2>
            </div>

            <button
                type="button"
                data-notification-close
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-400 transition hover:bg-gray-50 hover:text-gray-700"
                aria-label="Tutup notifikasi"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
            @forelse ($notifications as $notification)
                @php
                    $type = $notification['type'] ?? 'info';
                    $tone = match ($type) {
                        'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'fa-circle-check'],
                        'cancel' => ['bg' => 'bg-red-50', 'text' => 'text-red-500', 'icon' => 'fa-calendar-xmark'],
                        default => ['bg' => 'bg-blue-50', 'text' => 'text-[#58A7F7]', 'icon' => 'fa-bell'],
                    };
                    $notificationDate = \Carbon\Carbon::parse($notification['date']);
                @endphp

                <article class="flex gap-3 border-b border-gray-100 py-4 last:border-b-0">
                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $tone['bg'] }} {{ $tone['text'] }}">
                        <i class="fa-regular {{ $tone['icon'] }}"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold leading-snug text-black">{{ $notification['title'] }}</h3>
                            <span class="shrink-0 whitespace-nowrap text-xs text-gray-400">
                                {{ $notificationDate->isToday() ? 'Hari ini' : $notificationDate->translatedFormat('d M') }}
                            </span>
                        </div>

                        <p class="text-sm leading-relaxed text-[#777777]">{{ $notification['message'] }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $notificationDate->translatedFormat('H:i') }} WIB</p>
                    </div>
                </article>
            @empty
                <div class="flex h-full min-h-[320px] flex-col items-center justify-center text-center">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-[#58A7F7]">
                        <i class="fa-regular fa-bell-slash text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Belum ada notifikasi</p>
                    <p class="mt-1 max-w-[250px] text-xs leading-relaxed text-gray-400">
                        Update jadwal temu dan aktivitas pasien akan tampil di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <form
            method="POST"
            action="{{ route('pasien.layanan.ulasan.store') }}"
            class="w-full max-w-[460px] rounded-2xl bg-white p-6 shadow-xl"
        >
            @csrf

            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Buat Ulasan</h2>
                    <p class="mt-1 text-sm text-gray-400">Bagikan pengalaman layanan yang benar-benar Anda rasakan.</p>
                </div>

                <button type="button" data-review-close class="text-gray-400 transition hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="mb-2 block text-sm font-medium">Rating</label>
            <div class="mb-5 grid grid-cols-5 gap-2">
                @for ($rating = 1; $rating <= 5; $rating++)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="rating"
                            value="{{ $rating }}"
                            class="peer sr-only"
                            {{ (int) old('rating', 5) === $rating ? 'checked' : '' }}
                        >
                        <span class="flex h-11 items-center justify-center rounded-xl border border-gray-200 text-sm font-semibold text-gray-500 transition peer-checked:border-yellow-300 peer-checked:bg-yellow-50 peer-checked:text-yellow-500">
                            <i class="fa-solid fa-star mr-1"></i>
                            {{ $rating }}
                        </span>
                    </label>
                @endfor
            </div>

            <label for="reviewText" class="mb-2 block text-sm font-medium">Ulasan</label>
            <textarea
                id="reviewText"
                name="text"
                required
                minlength="8"
                maxlength="1000"
                class="h-32 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
                placeholder="Tulis pengalaman Anda..."
            >{{ old('text') }}</textarea>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" data-review-close class="rounded-xl border border-gray-200 px-5 py-3 text-sm text-gray-600 transition hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-blue-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-600">
                    Kirim Ulasan
                </button>
            </div>
        </form>
    </div>

    <!-- Edit Review Modal -->
    <div id="editReviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <form
            id="editReviewForm"
            method="POST"
            action=""
            class="w-full max-w-[460px] rounded-2xl bg-white p-6 shadow-xl"
        >
            @csrf
            @method('PUT')

            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Edit Ulasan</h2>
                    <p class="mt-1 text-sm text-gray-400">Ubah ulasan Anda untuk pengalaman layanan ini.</p>
                </div>

                <button type="button" data-edit-review-close class="text-gray-400 transition hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <label class="mb-2 block text-sm font-medium">Rating</label>
            <div class="mb-5 grid grid-cols-5 gap-2">
                @for ($rating = 1; $rating <= 5; $rating++)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="rating"
                            value="{{ $rating }}"
                            id="editRating{{ $rating }}"
                            class="peer sr-only"
                        >
                        <span class="flex h-11 items-center justify-center rounded-xl border border-gray-200 text-sm font-semibold text-gray-500 transition peer-checked:border-yellow-300 peer-checked:bg-yellow-50 peer-checked:text-yellow-500">
                            <i class="fa-solid fa-star mr-1"></i>
                            {{ $rating }}
                        </span>
                    </label>
                @endfor
            </div>

            <label for="editReviewText" class="mb-2 block text-sm font-medium">Ulasan</label>
            <textarea
                id="editReviewText"
                name="text"
                required
                minlength="8"
                maxlength="1000"
                class="h-32 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
                placeholder="Tulis pengalaman Anda..."
            ></textarea>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" data-edit-review-close class="rounded-xl border border-gray-200 px-5 py-3 text-sm text-gray-600 transition hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-blue-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-600">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</body>
</html>
