import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests',
    timeout: 30_000,
    retries: process.env.CI ? 2 : 0,
    reporter: process.env.CI
        ? [['list'], ['junit', { outputFile: 'test-results/results.xml' }]]
        : [['list']],
    use: {
        baseURL: process.env.BASE_URL ?? 'http://simple-rest-api.ddev.site',
        extraHTTPHeaders: {
            Accept: 'application/json',
        },
    },
});
