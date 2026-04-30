<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan - MediHub</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-white font-[Poppins] text-[#111827]">
    <div class="grid min-h-screen grid-cols-[220px_1fr_390px] overflow-hidden">
        <aside class="flex flex-col justify-between border-r border-gray-200 px-7 py-8">
            <div>
                <div class="mb-10">
                    <img src="{{ asset('images/Medihub.png') }}" alt="Logo MediHub" class="h-14 w-auto object-contain">
                </div>

                <p class="mb-6 text-lg font-semibold">Menu</p>

                <nav class="flex flex-col gap-7 text-[15px]">
                    <a href="{{ route('pasien.beranda') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-solid fa-house"></i>
                        Beranda
                    </a>

                    <a href="{{ route('pasien.layanan') }}" class="flex items-center gap-3 font-medium text-blue-500">
                        <i class="fa-solid fa-bed-pulse"></i>
                        Layanan
                    </a>

                    <a href="#" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-clock"></i>
                        Riwayat
                    </a>

                    <a href="{{ route('pasien.profile') }}" class="flex items-center gap-3 text-gray-500">
                        <i class="fa-regular fa-user"></i>
                        Profil
                    </a>
                </nav>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="w-full rounded-xl border border-gray-200 px-4 py-3 text-left text-sm text-gray-500">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>
                    Keluar
                </button>
            </form>
        </aside>

        <main class="overflow-y-auto bg-[#fbfbfb] px-6 py-10">
            <div class="mb-5 flex items-start justify-between gap-6">
                <div>
                    <p class="mb-1 text-2xl font-semibold text-blue-400">{{ $hospital['type'] }}</p>
                    <h1 class="text-[32px] font-semibold leading-tight">{{ $hospital['name'] }}</h1>
                </div>

                <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500">
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

                    <button class="h-12 w-12 rounded-xl border border-gray-200 bg-white text-gray-500">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </button>
                </div>
            </section>

            <section class="mb-7">
                <h2 class="mb-4 text-lg font-semibold">Kategori Poli</h2>

                <div class="flex gap-6 overflow-x-auto px-2 pt-2 pb-4">
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
        </main>

        <aside class="flex h-screen flex-col border-l border-gray-200 bg-white px-7 py-10">
            <h2 class="mb-6 text-lg font-semibold">Ulasan Pasien</h2>

            <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                @foreach ($reviews as $review)
                    <article class="border-b border-gray-100 pb-5 mb-5">
                        <div class="mb-3 flex items-start gap-3">
                            <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover">

                            <div>
                                <h3 class="text-sm font-medium">{{ $review['name'] }}</h3>
                                <p class="text-sm text-gray-500">
                                    <i class="fa-solid fa-star text-yellow-400"></i>
                                    {{ $review['rating'] }}
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

            <button class="mt-5 rounded-xl bg-blue-400 px-5 py-4 text-sm font-medium text-white shadow-md">
                Buat Ulasan
            </button>
        </aside>
    </div>

</body>
</html>
