<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Layanan - MediHub</title>

    @vite(['resources/css/app.css', 'resources/css/pasien/ulasan.css', 'resources/js/pasien/ulasan.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <x-pasien.sidebar active="layanan" />

    <div class="flex h-screen overflow-hidden bg-[#FBFBFB] pl-[220px]">
        <main class="min-w-0 flex-1 overflow-y-auto bg-[#FBFBFB] px-6 py-8">
            <div class="w-full max-w-[980px]">
                <div class="mb-5 flex items-start justify-between gap-6">
                    <div>
                        <p class="mb-1 text-2xl font-semibold text-blue-400">{{ $hospital['type'] }}</p>
                        <h1 class="text-[32px] font-semibold leading-tight">{{ $hospital['name'] }}</h1>
                    </div>

                    <button class="h-12 w-12 shrink-0 rounded-xl border border-gray-200 bg-white text-gray-500">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                </div>

                <section class="mb-8">
                    <div class="mb-5 flex flex-wrap items-end gap-6">
                        <div>
                            <p class="mb-1 text-sm text-gray-500">Jam Operasional</p>
                            <p class="text-xl font-semibold">{{ $hospital['operational_hour'] }}</p>
                        </div>

                        <div class="h-12 w-px bg-gray-200"></div>

                        <div class="text-2xl font-semibold">
                            IGD <span class="text-blue-700">{{ $hospital['emergency_hour'] }}</span>
                        </div>

                        <div class="ml-auto flex items-center gap-3 text-2xl">
                            <i class="fa-solid fa-phone text-gray-600"></i>
                            <span>{{ $hospital['phone'] }}</span>
                        </div>
                    </div>

                    <p class="mb-7 max-w-3xl text-justify text-sm leading-relaxed text-gray-500">
                        <span class="font-semibold">Rumah sakit umum</span> {{ $hospital['description'] }}
                    </p>

                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium">
                            <i class="fa-solid fa-location-dot mr-2 text-gray-500"></i>
                            {{ $hospital['address'] }}
                        </p>

                        <button class="h-12 w-12 shrink-0 rounded-xl border border-gray-200 bg-white text-gray-500">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </button>
                    </div>
                </section>

                <section class="mb-7">
                    <h2 class="mb-4 text-lg font-semibold">Kategori Poli</h2>

                    <div class="flex gap-6 overflow-x-auto px-2 pb-4 pt-2">
                        @foreach ($categories as $category)
                            <div class="group min-w-[82px] text-center">
                                <div class="mx-auto mb-2 flex h-[72px] w-[72px] items-center justify-center rounded-full bg-blue-400 text-3xl text-white transition-transform duration-200 group-hover:scale-105">
                                    <img src="{{ asset('images/categories/' . $category['icon']) }}" alt="{{ $category['nama'] }}" class="h-9 w-9 object-contain">
                                </div>

                                <p class="text-sm leading-tight">{{ $category['nama'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mb-8">
                    <h2 class="mb-4 text-lg font-semibold">Dokter Pilihan Pasien</h2>

                    <div class="flex gap-4 overflow-x-auto pb-4">
                        @foreach ($doctors as $doctor)
                            <a href="{{ $doctor['id'] ? route('pasien.booking.create', ['doctor_id' => $doctor['id']]) : route('pasien.booking.create') }}" class="min-w-[215px] overflow-hidden rounded-xl bg-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
                                <img src="{{ $doctor['foto'] }}" alt="{{ $doctor['nama'] }}" class="h-[155px] w-full bg-blue-100 object-cover">

                                <div class="p-4">
                                    <h3 class="mb-1 text-[15px] font-semibold leading-snug">{{ $doctor['nama'] }}</h3>
                                    <p class="mb-3 text-xs leading-snug text-gray-500">{{ $doctor['spesialis'] }}</p>

                                    <div class="flex gap-3 text-xs text-gray-500">
                                        <span><i class="fa-solid fa-star text-yellow-400"></i> {{ $doctor['rating'] }}</span>
                                        <span>{{ $doctor['pasien'] }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold">Fasilitas Unggulan</h2>
                    <ul class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm text-gray-500">
                        @foreach ($simpleFacilities as $facility)
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                {{ $facility }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>
        </main>

{{-- SIDEBAR KANAN: Ulasan Pasien --}}
        <aside class="h-screen w-[360px] shrink-0 overflow-y-auto border-l border-gray-200 bg-white px-6 py-8 xl:w-[390px] xl:px-7">
            <div class="flex min-h-full flex-col">
                <h2 class="mb-2 text-lg font-semibold">Ulasan Pasien</h2>
                <p class="mb-5 text-xs text-gray-400">Ulasan dari pasien yang sudah pernah berkunjung.</p>

                <div id="ulasan-list" class="min-h-0 flex-1 overflow-y-auto pr-1">
                    {{-- Ulasan statis sebagai fallback sebelum ada data dari Firestore --}}
                    @foreach ($reviews as $review)
                        <article class="mb-5 border-b border-gray-100 pb-5">
                            <div class="mb-3 flex items-start gap-3">
                                <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover">

                                <div>
                                    <h3 class="text-sm font-medium">{{ $review['name'] }}</h3>
                                    <p class="flex items-center gap-1 text-sm text-yellow-500">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa-{{ $i <= $review['rating'] ? 'solid' : 'regular' }} fa-star text-xs"></i>
                                        @endfor
                                        <span class="ml-1 text-xs text-gray-500">{{ $review['rating'] }}</span>
                                    </p>
                                </div>
                            </div>

                            <p class="mb-4 text-sm leading-snug text-gray-900">{{ $review['text'] }}</p>

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $review['date'] }}</span>
                                <span><i class="fa-regular fa-heart mr-1"></i>{{ $review['likes'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Tombol Buat Ulasan --}}
                <button
                    id="btn-buat-ulasan"
                    class="mt-5 w-full rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium text-white shadow-md transition hover:bg-blue-500 active:scale-95"
                >
                    <i class="fa-solid fa-pen mr-2"></i>Buat Ulasan
                </button>
            </div>
        </aside>
    </div>
{{-- ═══ MODAL BUAT ULASAN (PBI-23) ═══ --}}
    <div id="ulasan-overlay" class="ulasan-overlay" role="dialog" aria-modal="true" aria-labelledby="ulasan-modal-title">
        <div class="ulasan-dialog">
            <h2 id="ulasan-modal-title" class="ulasan-dialog-title">
                <i class="fa-solid fa-star text-yellow-400 mr-2"></i>Buat Ulasan
            </h2>

            {{-- Error --}}
            <p id="ulasan-error" class="ulasan-error"></p>

            <form
                id="ulasan-form"
                action="{{ route('pasien.ulasan.store') }}"
                method="POST"
                novalidate
            >
                @csrf

                {{-- Pilih Dokter --}}
                <label class="mb-2 block text-sm font-semibold text-gray-700" for="ulasan-doctor-id">
                    Dokter yang dikunjungi
                </label>
                <select id="ulasan-doctor-id" name="doctor_id" class="ulasan-doctor-select" required>
                    <option value="">-- Pilih dokter --</option>
                    @foreach ($doctors as $doctor)
                        @if($doctor['id'])
                            <option value="{{ $doctor['id'] }}">{{ $doctor['nama'] }} — {{ $doctor['spesialis'] }}</option>
                        @endif
                    @endforeach
                </select>

                {{-- Rating Bintang --}}
                <span class="ulasan-stars-label">Rating</span>
                <div id="ulasan-stars" class="ulasan-stars" role="group" aria-label="Pilih rating bintang">
                    @for ($i = 1; $i <= 5; $i++)
                        <span
                            class="ulasan-star"
                            data-value="{{ $i }}"
                            role="radio"
                            aria-label="{{ $i }} bintang"
                            tabindex="0"
                        >&#9733;</span>
                    @endfor
                </div>
                <input type="hidden" id="ulasan-rating" name="rating" value="0">

                {{-- Textarea --}}
                <label class="mb-2 block text-sm font-semibold text-gray-700" for="ulasan-text">
                    Ceritakan pengalamanmu
                </label>
                <textarea
                    id="ulasan-text"
                    name="ulasan"
                    class="ulasan-textarea"
                    placeholder="Tulis ulasan jujur kamu tentang layanan dokter ini..."
                    maxlength="1000"
                ></textarea>
                <span id="ulasan-char-count" class="ulasan-char-count">0/1000</span>

                {{-- Actions --}}
                <div class="ulasan-actions">
                    <button type="button" id="ulasan-cancel" class="ulasan-btn-cancel">Batal</button>
                    <button type="submit" id="ulasan-submit" class="ulasan-btn-submit">
                        <i class="fa-solid fa-paper-plane mr-1"></i>Kirim Ulasan
                    </button>
                </div>
            </form>

            {{-- Tombol close X --}}
            <button
                id="ulasan-close"
                aria-label="Tutup modal"
                style="position:absolute;top:16px;right:20px;background:none;border:none;font-size:20px;color:#9ca3af;cursor:pointer;line-height:1;"
            >&#x2715;</button>
        </div>
    </div>
</body>
</html>
