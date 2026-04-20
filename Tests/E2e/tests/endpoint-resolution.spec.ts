import { test, expect } from '@playwright/test';

/**
 * E2E tests: endpoint resolution (#13)
 *
 * Verifies that the extension correctly resolves API endpoints
 * for all HTTP methods, returns valid JSON, and handles unknown
 * paths with a 404 response.
 *
 * All tests use the stable /e2e/ping endpoints from E2eTestController,
 * which is registered only in Development context.
 */

test.describe('Endpoint resolution', () => {
    test.describe('Happy path — HTTP methods', () => {
        const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as const;

        for (const method of methods) {
            test(`${method} /api/e2e/ping → 200 with JSON body`, async ({ request }) => {
                const response = await request.fetch('/api/e2e/ping', { method });

                expect(response.status()).toBe(200);
                expect(response.headers()['content-type']).toContain('application/json');

                const body = await response.json();
                expect(body.ok).toBe(true);
                expect(body.method).toBe(method);
            });
        }
    });

    test.describe('Error cases', () => {
        test('correct path, wrong HTTP method → 404', async ({ request }) => {
            // /api/e2e/params/int/{value} only accepts GET
            const response = await request.post('/api/e2e/params/int/42');

            expect(response.status()).toBe(404);
        });

        test('404 response body is valid JSON', async ({ request }) => {
            const response = await request.get('/api/e2e/does-not-exist');

            expect(response.status()).toBe(404);
            expect(response.headers()['content-type']).toContain('application/json');
            await response.json(); // throws if body is not valid JSON
        });
    });


});
