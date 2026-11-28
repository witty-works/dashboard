import {defineConfig} from 'vitest/config';

// The unit tests need none of vite.config.js: its Laravel plugin starts a
// dev server, which refuses to run in CI.
export default defineConfig({
    test: {
        include: ['resources/js/**/*.test.js'],
    },
});
