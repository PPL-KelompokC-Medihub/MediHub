import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/auth/dokter/sign-in.js',
                'resources/js/auth/dokter/sign-up.js',
                'resources/js/auth/pasien/sign-in.js',
                'resources/js/auth/pasien/sign-up.js',
                'resources/css/dokter/profile-form.css',
                'resources/js/dokter/profile-form.js',
                'resources/js/dokter/profile-expertise.js',
            ],
            refresh: true,
        }),
    ],
});
