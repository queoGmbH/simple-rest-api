import { test, expect } from '@playwright/test';

/**
 * E2E tests: parameter handling (#14)
 *
 * Verifies that the extension correctly coerces URL path parameters to their
 * declared PHP types (int, float, bool, string), returns properly typed values
 * in the JSON response, and returns 400 with a descriptive JSON error body when
 * coercion fails.
 *
 * All tests use the stable /e2e/params/* endpoints from E2eTestController,
 * which is registered only in Development context.
 *
 * Error message format is determined by EndpointParameterResolver:
 *   int:   "Parameter '%s' must be an integer, got: '%s'"
 *   float: "Parameter '%s' must be a float, got: '%s'"
 *   bool:  "Parameter '%s' must be a boolean (1/0, true/false, yes/no, on/off), got: '%s'"
 */

test.describe('Parameter handling', () => {
    test.describe('int parameter — valid coercion', () => {
        test('positive integer → 200, value is number', async ({ request }) => {
            const response = await request.get('/api/e2e/params/int/42');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(42);
            expect(body.type).toBe('int');
        });

        test('negative integer → 200, value is negative number', async ({ request }) => {
            const response = await request.get('/api/e2e/params/int/-7');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(-7);
            expect(body.type).toBe('int');
        });

        test('zero → 200, value is 0 (not falsy confusion)', async ({ request }) => {
            const response = await request.get('/api/e2e/params/int/0');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(0);
            expect(body.type).toBe('int');
        });
    });

    test.describe('float parameter — valid coercion', () => {
        test('decimal float → 200, value is float', async ({ request }) => {
            const response = await request.get('/api/e2e/params/float/3.14');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBeCloseTo(3.14);
            expect(body.type).toBe('float');
        });

        test('integer-as-float → 200, value is numeric', async ({ request }) => {
            const response = await request.get('/api/e2e/params/float/10');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(10);
            expect(body.type).toBe('float');
        });

        test('negative float → 200, value is negative', async ({ request }) => {
            const response = await request.get('/api/e2e/params/float/-1.5');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBeCloseTo(-1.5);
            expect(body.type).toBe('float');
        });
    });

    test.describe('bool parameter — valid coercion', () => {
        test('"true" → 200, value is boolean true', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/true');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(true);
            expect(body.type).toBe('bool');
        });

        test('"false" → 200, value is boolean false (not the string)', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/false');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(false);
            expect(body.type).toBe('bool');
        });

        test('"1" → 200, value is boolean true', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/1');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(true);
            expect(body.type).toBe('bool');
        });

        test('"0" → 200, value is boolean false', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/0');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(false);
            expect(body.type).toBe('bool');
        });

        test('"yes" → 200, value is boolean true', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/yes');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(true);
            expect(body.type).toBe('bool');
        });

        test('"no" → 200, value is boolean false', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/no');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(false);
            expect(body.type).toBe('bool');
        });
    });

    test.describe('string parameter — valid coercion', () => {
        test('regular string → 200, value is the string', async ({ request }) => {
            const response = await request.get('/api/e2e/params/string/hello');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe('hello');
            expect(body.type).toBe('string');
        });

        test('very long string (1000 chars) → 200, no crash', async ({ request }) => {
            const longString = 'a'.repeat(1000);
            const response = await request.get(`/api/e2e/params/string/${longString}`);

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.value).toBe(longString);
            expect(body.type).toBe('string');
        });
    });

    test.describe('multi-parameter endpoint', () => {
        test('GET /api/e2e/params/multi/42/hello → 200, correct intVal and stringVal', async ({ request }) => {
            const response = await request.get('/api/e2e/params/multi/42/hello');

            expect(response.status()).toBe(200);
            const body = await response.json();
            expect(body.intVal).toBe(42);
            expect(body.stringVal).toBe('hello');
        });
    });

    test.describe('Invalid parameters — 400 responses', () => {
        test('int with "abc" → 400 with JSON error body', async ({ request }) => {
            const response = await request.get('/api/e2e/params/int/abc');

            expect(response.status()).toBe(400);
            expect(response.headers()['content-type']).toContain('application/json');
            const body = await response.json();
            expect(typeof body.error).toBe('string');
            expect(body.error.length).toBeGreaterThan(0);
        });

        test('int with "abc" → 400 error message contains parameter name', async ({ request }) => {
            const response = await request.get('/api/e2e/params/int/abc');

            const body = await response.json();
            // EndpointParameterResolver: "Parameter 'value' must be an integer, got: 'abc'"
            expect(body.error).toContain('value');
        });

        test('float with "abc" → 400 with JSON error body', async ({ request }) => {
            const response = await request.get('/api/e2e/params/float/abc');

            expect(response.status()).toBe(400);
            expect(response.headers()['content-type']).toContain('application/json');
            const body = await response.json();
            expect(typeof body.error).toBe('string');
            expect(body.error.length).toBeGreaterThan(0);
        });

        test('float with "abc" → 400 error message contains parameter name', async ({ request }) => {
            const response = await request.get('/api/e2e/params/float/abc');

            const body = await response.json();
            // EndpointParameterResolver: "Parameter 'value' must be a float, got: 'abc'"
            expect(body.error).toContain('value');
        });

        test('bool with "maybe" → 400 with JSON error body', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/maybe');

            expect(response.status()).toBe(400);
            expect(response.headers()['content-type']).toContain('application/json');
            const body = await response.json();
            expect(typeof body.error).toBe('string');
            expect(body.error.length).toBeGreaterThan(0);
        });

        test('bool with "maybe" → 400 error message contains parameter name', async ({ request }) => {
            const response = await request.get('/api/e2e/params/bool/maybe');

            const body = await response.json();
            // EndpointParameterResolver: "Parameter 'value' must be a boolean (1/0, true/false, yes/no, on/off), got: 'maybe'"
            expect(body.error).toContain('value');
        });
    });
});
