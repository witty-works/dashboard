import {defineConfig, devices} from '@playwright/test';

// Browser tests of what PHPUnit cannot see: the rendered pages' accessibility
// (axe), keyboard and focus behaviour, reflow and the Content-Security-Policy.
// They need the database seeded with BrowserTestSeeder and the built assets
// (npm run build); the CI job sets both up (.github/workflows/ci.yaml).
const port = Number(process.env.BROWSER_TEST_PORT || 8000);

export default defineConfig({
    testDir: 'tests/browser',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: process.env.CI ? [['list'], ['github']] : 'list',
    use: {
        baseURL: `http://127.0.0.1:${port}`,
        trace: 'retain-on-failure',
    },
    projects: [
        // Signs in once; the browser tests reuse its session.
        {name: 'setup', testMatch: /.*\.setup\.js/},
        {name: 'chromium', use: {...devices['Desktop Chrome']}, dependencies: ['setup'], testIgnore: /.*\.setup\.js/},
    ],
    webServer: {
        // PHP's built-in server with Laravel's router script rather than
        // `artisan serve`, which drops the environment set here.
        // It runs in public/, where the router script looks for index.php.
        command: `php -d display_errors=stderr -S 127.0.0.1:${port} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
        cwd: 'public',
        url: `http://127.0.0.1:${port}/up`,
        reuseExistingServer: !process.env.CI,
        env: {
            APP_URL: `http://127.0.0.1:${port}`,
            SESSION_SECURE_COOKIE: 'false',
            POSTHOG_ENABLED: 'false',
            APP_NLP_API_SYNC_ENDPOINT: '',
            APP_NLP_API_SYNC_CONFIGS: 'false',
            MAIL_MAILER: 'log',
            // A developer tool drawn over every page, not what people see.
            DEBUGBAR_ENABLED: 'false',
        },
    },
});
