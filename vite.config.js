import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        sourcemap: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/write.js',
                'resources/js/analytics.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: [
            // One moment for the analytics page: by default Vite resolves
            // 'moment' to dist/moment.js, while the locale files load
            // moment.js, so the locales would land on a copy the page and
            // Chart.js do not use. tests/js/analytics-bundle.mjs checks it.
            {find: /^moment$/, replacement: 'moment/moment.js'},
        ],
    },
    define: {
        _global: ({})
    }
});
