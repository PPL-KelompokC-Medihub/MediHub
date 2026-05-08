<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pasien - MediHub</title>

    @vite(['resources/css/app.css', 'resources/js/pasien/profile.js'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="font-[Poppins] bg-white text-gray-900">
    <div data-patient-profile></div>
    <div class="grid h-screen grid-cols-[220px_1fr_300px] overflow-hidden">
        <x-pasien.sidebar active="profil" />

        <main class="h-screen overflow-y-auto bg-white px-8 py-10">
            <header class="mb-8">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img 
                            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=300&auto=format&fit=crop"
                            class="h-28 w-28 rounded-full object-cover"
                            alt="Foto Profil"
                        >

                        <button class="absolute bottom-1 right-1 flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-white">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </div>

                    <div class="pt-1">
                        <h1 class="text-xl font-semibold">
                            {{ $user->fullname ?? $user->name ?? 'Pasien' }}
                        </h1>

                        <div class="mt-2 flex gap-6 text-sm text-gray-500">
                            <span>
                                <i class="fa-regular fa-calendar mr-2"></i>
                                Bergabung {{ isset($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->translatedFormat('F Y') : '-' }}
                            </span>

                            <span>
                                <i class="fa-solid fa-location-dot mr-2"></i>
                                Bandung, Indonesia
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="rounded-2xl border border-gray-200 p-6">
            <form action="{{ route('pasien.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Informasi Pribadi</h2>

                    <button 
                        type="button"
                        id="editProfileBtn"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-500"
                    >
                        Edit <i class="fa-regular fa-pen-to-square ml-1"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Nama Lengkap</label>
                        <input 
                            name="fullname"
                            value="{{ $user->fullname ?? '' }}"
                            readonly
                            class="profile-input w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none"
                        >
                    </div>

                    <input type="hidden" name="email" value="{{ $user->email ?? '' }}">

                    <div>
                        <label class="mb-2 block text-sm font-medium">Umur Pasien</label>
                        <div class="flex items-center rounded-xl border border-gray-200 px-4 py-3">
                            <input 
                                name="umur"
                                value="{{ $user->umur ?? $user->age ?? '' }}"
                                placeholder="Belum diisi"
                                readonly
                                class="profile-input w-full text-sm outline-none"
                            >
                            <span class="text-sm text-gray-400">Tahun</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Berat Badan</label>
                        <div class="flex items-center rounded-xl border border-gray-200 px-4 py-3">
                            <input 
                                name="weight"
                                value="{{ $user->weight ?? '' }}"
                                placeholder="Belum diisi"
                                readonly
                                class="profile-input w-full text-sm outline-none"
                            >
                            <span class="text-sm text-gray-400">kg</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Tinggi Badan</label>
                        <div class="flex items-center rounded-xl border border-gray-200 px-4 py-3">
                            <input 
                                name="height"
                                value="{{ $user->height ?? '' }}"
                                placeholder="Belum diisi"
                                readonly
                                class="profile-input w-full text-sm outline-none"
                            >
                            <span class="text-sm text-gray-400">cm</span>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="mb-2 block text-sm font-medium">Jenis Kelamin</label>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3">
                                <input
                                    type="radio"
                                    name="gender"
                                    value="Perempuan"
                                    disabled
                                    class="profile-input"
                                    {{ ($user->gender ?? '') === 'Perempuan' ? 'checked' : '' }}
                                >
                                <div>
                                    <div class="text-lg">♀</div>
                                    <p class="text-sm text-gray-500">Perempuan</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3">
                                <input
                                    type="radio"
                                    name="gender"
                                    value="Pria"
                                    disabled
                                    class="profile-input"
                                    {{ ($user->gender ?? '') === 'Pria' ? 'checked' : '' }}
                                >
                                <div>
                                    <div class="text-lg">♂</div>
                                    <p class="text-sm text-gray-500">Pria</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="mb-2 block text-sm font-medium">Golongan Darah</label>

                        <div class="flex items-center gap-5">
                            @foreach (['A', 'B', 'AB', 'O'] as $blood)
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        name="blood_type"
                                        value="{{ $blood }}"
                                        disabled
                                        class="profile-input"
                                        {{ ($user->blood_type ?? '') === $blood ? 'checked' : '' }}
                                    >
                                    {{ $blood }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="mb-2 block text-sm font-medium">Riwayat Alergi Obat</label>
                        <textarea 
                            name="allergy_history"
                            readonly
                            placeholder="Beritahu dokter riwayat alergi anda"
                            class="profile-input h-24 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none"
                        >{{ $user->allergy_history ?? '' }}</textarea>
                        <label class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="no_allergy"
                                value="1"
                                disabled
                                class="profile-input"
                                {{ ($user->no_allergy ?? false) ? 'checked' : '' }}
                            >
                            Tidak ada
                        </label>
                        <div id="saveProfileWrapper" class="mt-5 hidden justify-end">
                            <button 
                                type="submit"
                                class="rounded-xl bg-blue-500 px-6 py-3 text-sm font-medium text-white"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            </section>

            <section class="mt-5 rounded-2xl border border-gray-200 p-6">
                <form action="{{ route('pasien.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="fullname" value="{{ $user->fullname ?? '' }}">
                    <input type="hidden" name="email" value="{{ $user->email ?? '' }}">

                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Alamat</h2>

                        <button
                            type="button"
                            id="editAddressBtn"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-500"
                        >
                            Edit <i class="fa-regular fa-pen-to-square ml-1"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Negara</label>

                            <input
                                name="country"
                                value="{{ $user->country ?? 'Indonesia' }}"
                                readonly
                                class="address-input w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Kota</label>

                            <input
                                name="city"
                                value="{{ $user->city ?? 'Bandung' }}"
                                readonly
                                class="address-input w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Kode Pos</label>

                            <input
                                name="code_pos"
                                value="{{ $user->code_pos ?? '' }}"
                                placeholder="Belum diisi"
                                readonly
                                class="address-input w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none"
                            >
                        </div>
                    </div>

                    <div id="saveAddressWrapper" class="mt-5 hidden justify-end">
                        <button
                            type="submit"
                            class="rounded-xl bg-blue-500 px-6 py-3 text-sm font-medium text-white"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </section>
        </main>

        <aside class="sticky top-0 h-screen overflow-hidden border-l border-gray-200 bg-white px-6 py-8">
            <h2 class="mb-5 text-lg font-semibold">Pengaturan Akun</h2>

            <div class="mb-6 rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-user rounded-lg border border-gray-200 p-2"></i>
                        <div>
                            <p class="text-sm font-medium">Pusat Akun</p>
                            <p class="text-xs text-gray-400">Kata sandi, keamanan, dan detail pribadi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mb-4 text-sm text-gray-400">Informasi & Layanan</p>

            <div class="flex flex-col gap-5 text-sm">
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-solid fa-person"></i> Aksesibilitas</a>
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-regular fa-bell"></i> Notifikasi</a>
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-solid fa-globe"></i> Bahasa & Tampilan</a>
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-solid fa-shield-halved"></i> Privasi</a>
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-regular fa-circle-question"></i> Bantuan</a>
                <a href="#" class="group flex items-center gap-3 transition-all duration-200 hover:-translate-y-[2px] hover:text-[#58A7F7]"><i class="fa-solid fa-key"></i> Izin Aplikasi & Website</a>
            </div>

            <p class="mb-4 mt-7 text-sm text-gray-400">Login</p>

            <div class="flex flex-col gap-5 text-sm">
                <a href="#" class="flex items-center gap-3 text-blue-500">
                    <i class="fa-solid fa-plus"></i> Tambah Akun
                </a>

                <button 
                    type="button"
                    data-modal-open="deleteAccountModal"
                    class="group flex items-center gap-3 text-red-500 transition-all duration-200 hover:-translate-y-[2px] hover:text-red-600"
                >
                    <i class="fa-regular fa-trash-can transition-all duration-200 group-hover:text-red-600"></i>
                    <span>Hapus Akun</span>
                </button>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="flex items-center gap-3 text-gray-700">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <div id="deleteAccountModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="w-[420px] rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="mb-3 text-xl font-semibold text-red-500">Hapus Akun?</h2>

            <p class="mb-6 text-sm leading-relaxed text-gray-500">
                Akun Anda akan dihapus secara permanen. Data login dan data profil tidak dapat dikembalikan.
            </p>

            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    data-modal-close="deleteAccountModal"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600"
                >
                    Batal
                </button>

                <form action="{{ route('pasien.destroy-account') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <button 
                        type="submit"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm text-white"
                    >
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
