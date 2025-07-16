import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/auth.css',
                'resources/css/admin.css',
                'resources/css/dashboard.css',
            ],
            refresh: true,
        }),
    ],
});
