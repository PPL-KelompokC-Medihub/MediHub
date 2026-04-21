import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/auth/sign-in.js',
                'resources/js/auth/sign-up.js',
                'resources/js/auth-pasien/pasien-sign-in.js',
                'resources/js/auth-pasien/pasien-sign-up.js',
                'resources/css/doctor/profile-form.css',
                'resources/js/doctor/profile-form.js',
                'resources/js/doctor/profile-expertise.js',
            ],
            refresh: true,
        }),
    ],
});
