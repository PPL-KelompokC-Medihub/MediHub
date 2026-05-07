import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        // tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/auth/dokter/sign-in.js',
                'resources/js/auth/dokter/sign-up.js',
                'resources/js/auth/pasien/sign-in.js',
                'resources/js/auth/pasien/sign-up.js',
                'resources/css/pasien/beranda.css',
                'resources/js/pasien/beranda.js',
                'resources/js/pasien/profile.js',
                'resources/js/pasien/booking.js',
                'resources/css/dokter/dashboard.css',
                'resources/css/dokter/jadwal.css',
                'resources/js/dokter/jadwal.js',
                'resources/css/dokter/profile-form.css',
                'resources/js/dokter/profile-form.js',
                'resources/js/dokter/profile-expertise.js',
                'resources/css/dokter/profil.css',
            ],
            refresh: true,
        }),
    ],
});
