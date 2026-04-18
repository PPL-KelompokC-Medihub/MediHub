import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/doctor/profile-form.css',
                'resources/js/doctor/profile-form.js',
            ],
            refresh: true,
        }),
    ],
});
