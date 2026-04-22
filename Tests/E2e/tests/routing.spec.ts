import { test, expect } from '@playwright/test';

/**
 * E2E tests: routing and base path configuration (#15)
 *
 * Verifies that the SimpleRestApiEnhancer and ApiResolverMiddleware correctly
 * route requests based on the configured API base path, and that the
 * CacheHashFixer middleware allows API requests to bypass cHash enforcement.
 *
 * All tests use the stable /e2e/ping endpoint from E2eTestController,
 * which is registered only in Development context.
 *
 * Feasibility notes (assessed before writing):
 *
 * TESTABLE:
 *   - Default base path /api/ — site has no basePath setting, falls back to /api/
 *   - Non-API path bypass — requests without /api/ prefix pass through to TYPO3
 *   - cHash bypass — CacheHashFixer disables cHash enforcement for API paths
 *
 * TESTABLE (requires two-site CI fixture):
 *   - Custom base path /rest/ — a second TYPO3 site (rootPageId=10) with
 *     basePath='/rest/' coexists in the same instance; TYPO3's router selects
 *     the correct site via the SimpleRestApiEnhancer route patterns.
 */

test.describe('Routing — default base path /api/', () => {
    test('GET /api/e2e/ping → JSON body confirms endpoint was reached', async ({ request }) => {
        // Arrange
        // Act
        const response = await request.get('/api/e2e/ping');

        // Assert
        const body = await response.json();
        expect(body.ok).toBe(true);
    });

});

test.describe('Routing — non-API path bypass', () => {
    test('GET /e2e/ping (no /api/ prefix) → does not reach API middleware (404)', async ({ request }) => {
        // Arrange — path lacks the /api/ prefix; ApiResolverMiddleware.isApiRequest()
        // returns false and delegates to the TYPO3 page handler, which returns 404.
        // Act
        const response = await request.get('/e2e/ping');

        // Assert
        expect(response.status()).toBe(404);
    });

    test('GET /e2e/ping (no /api/ prefix) → response is NOT the API JSON format', async ({ request }) => {
        // Arrange — TYPO3's own error handler responds; the API { ok, method } shape
        // must not appear, confirming the request never reached ApiResolverMiddleware.
        // Act
        const response = await request.get('/e2e/ping');
        const body = await response.json();

        // Assert
        expect(body).not.toHaveProperty('ok');
        expect(body).not.toHaveProperty('method');
    });

    test('GET /rest/e2e/ping (wrong prefix) → does not reach API middleware (404)', async ({ request }) => {
        // Arrange — /rest/ is not the configured base path; request bypasses the API.
        // Act
        const response = await request.get('/rest/e2e/ping');

        // Assert
        expect(response.status()).toBe(404);
    });
});

test.describe('Routing — cHash bypass via CacheHashFixer', () => {
    // CacheHashFixer disables pageNotFoundOnCHashError and cacheHash.enforceValidation
    // for any request whose path starts with the configured API base path.
    // Without this middleware, TYPO3 would return 404 when query parameters are
    // present but no valid cHash is supplied.

    test('GET /api/e2e/ping?foo=bar (unknown query param, no cHash) → 200', async ({ request }) => {
        // Arrange — arbitrary query parameters without a cHash would normally trigger
        // TYPO3's cHash error handling; CacheHashFixer must suppress it for API paths.
        // Act
        const response = await request.get('/api/e2e/ping?foo=bar');

        // Assert
        expect(response.status()).toBe(200);
    });

    test('GET /api/e2e/ping?cHash=invalidhash → 200 (invalid cHash is tolerated)', async ({ request }) => {
        // Arrange — an explicitly wrong cHash value must not cause a 404 for API paths.
        // Act
        const response = await request.get('/api/e2e/ping?cHash=invalidhash');

        // Assert
        expect(response.status()).toBe(200);
    });

    test('GET /api/e2e/ping?foo=bar (with unknown query params) → correct JSON body', async ({ request }) => {
        // Arrange — query parameters must not corrupt the endpoint response body.
        // Act
        const response = await request.get('/api/e2e/ping?foo=bar');
        const body = await response.json();

        // Assert
        expect(body.ok).toBe(true);
        expect(body.method).toBe('GET');
    });
});

test.describe('Routing — custom base path /rest/', () => {
    // The CI fixture includes a second TYPO3 site (rootPageId=10) configured
    // with basePath='/rest/'. TYPO3's router selects this site for /rest/* paths
    // via the SimpleRestApiEnhancer, so both sites coexist on the same server.

    test('GET /rest/e2e/ping on a site with basePath=/rest/ → 200', async ({ request }) => {
        const response = await request.get('/rest/e2e/ping');
        expect(response.status()).toBe(200);
    });

    test('GET /api/e2e/ping on a site with basePath=/rest/ → 404 (wrong prefix)', async ({ request }) => {
        const response = await request.get('/api/e2e/ping');
        expect(response.status()).toBe(404);
    });
});

test.describe('Routing — multi-language path prefix', () => {
    // The site fixture now includes a second language with base: /en/.
    // Both ApiRequest and CacheHashFixer use the language attribute set by
    // TYPO3's SiteResolver to build the correct base path, so the API is
    // reachable under /en/api/ without any extension code changes.

    test('GET /en/api/e2e/ping on a site with language prefix /en/ → 200', async ({ request }) => {
        const response = await request.get('/en/api/e2e/ping');
        expect(response.status()).toBe(200);
    });

    test('GET /en/api/e2e/ping → correct JSON body', async ({ request }) => {
        const response = await request.get('/en/api/e2e/ping');
        const body = await response.json();
        expect(body.ok).toBe(true);
        expect(body.method).toBe('GET');
    });
});
