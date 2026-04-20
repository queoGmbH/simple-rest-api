import { test, expect } from '@playwright/test';

/**
 * E2E tests: ModifyApiResponseEvent (#16)
 *
 * Verifies that ModifyApiResponseEvent is dispatched after every successful
 * API endpoint invocation and that listeners can modify the response.
 *
 * The Dev-only E2eTestResponseListener adds two headers to every API response:
 *   X-E2E-Modified: true
 *   X-E2E-Endpoint: <endpoint path>
 *
 * Non-API requests must NOT carry these headers because the event is never
 * dispatched for them.
 *
 * All tests use the stable /e2e/* endpoints from E2eTestController, which is
 * registered only in Development context.
 */

test.describe('ModifyApiResponseEvent', () => {
    test.describe('X-E2E-Modified header — event is dispatched', () => {
        test('GET /api/e2e/ping → X-E2E-Modified: true is present', async ({ request }) => {
            // Arrange + Act
            const response = await request.get('/api/e2e/ping');

            // Assert
            expect(response.status()).toBe(200);
            expect(response.headers()['x-e2e-modified']).toBe('true');
        });

        test('POST /api/e2e/ping → X-E2E-Modified: true is present', async ({ request }) => {
            // Arrange + Act
            const response = await request.post('/api/e2e/ping');

            // Assert
            expect(response.status()).toBe(200);
            expect(response.headers()['x-e2e-modified']).toBe('true');
        });

        test('endpoint with parameters → X-E2E-Modified: true is present', async ({ request }) => {
            // Arrange + Act
            const response = await request.get('/api/e2e/params/int/42');

            // Assert
            expect(response.status()).toBe(200);
            expect(response.headers()['x-e2e-modified']).toBe('true');
        });
    });

    test.describe('X-E2E-Endpoint header — endpoint path is accessible in event', () => {
        test('GET /api/e2e/ping → X-E2E-Endpoint contains /e2e/ping', async ({ request }) => {
            // Arrange + Act
            const response = await request.get('/api/e2e/ping');

            // Assert
            expect(response.status()).toBe(200);
            expect(response.headers()['x-e2e-endpoint']).toBe('/e2e/ping');
        });

        test('GET /api/e2e/params/int/42 → X-E2E-Endpoint contains the parameterised path', async ({ request }) => {
            // Arrange + Act
            const response = await request.get('/api/e2e/params/int/42');

            // Assert
            expect(response.status()).toBe(200);
            expect(response.headers()['x-e2e-endpoint']).toBe('/e2e/params/int/{value}');
        });
    });

    test.describe('Non-API requests — event is NOT dispatched', () => {
        test('GET /e2e/ping without /api/ prefix → X-E2E-Modified header is absent', async ({ request }) => {
            // Arrange + Act — plain TYPO3 request, not routed through API middleware
            const response = await request.get('/e2e/ping');

            // Assert — the header must not appear on non-API responses
            expect(response.headers()['x-e2e-modified']).toBeUndefined();
        });
    });

    test.describe('Header present across HTTP methods', () => {
        const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as const;

        for (const method of methods) {
            test(`${method} /api/e2e/ping → X-E2E-Modified: true is present`, async ({ request }) => {
                // Arrange + Act
                const response = await request.fetch('/api/e2e/ping', { method });

                // Assert
                expect(response.status()).toBe(200);
                expect(response.headers()['x-e2e-modified']).toBe('true');
            });
        }
    });
});
