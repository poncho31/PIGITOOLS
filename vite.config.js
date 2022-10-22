import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/pigitools.css', 'resources/js/pigitools.js'],
            refresh: true,
        }),
    ],
});
