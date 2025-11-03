import { test, expect } from '@playwright/test';

test.describe('Query Parameters and Headers', () => {
  test('GET /api/test/search with query parameters', async ({ request }) => {
    const response = await request.get('/api/test/search', {
      params: {
        q: 'test query',
        limit: '20',
        offset: '10'
      }
    });

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.query).toBe('test query');
    expect(data.limit).toBe(20);
    expect(data.offset).toBe(10);
    expect(data.results).toHaveLength(2);
  });

  test('GET /api/test/search without query parameters uses defaults', async ({ request }) => {
    const response = await request.get('/api/test/search');

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.query).toBe('');
    expect(data.limit).toBe(10);
    expect(data.offset).toBe(0);
  });

  test('GET /api/test/headers checks request headers', async ({ request }) => {
    const response = await request.get('/api/test/headers', {
      headers: {
        'Authorization': 'Bearer test-token',
        'X-Custom-Header': 'custom-value'
      }
    });

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.headers.authorization).toBe(true);
    expect(data.headers.custom_header).toBe('custom-value');
  });

  test('GET /api/test/headers without Authorization header', async ({ request }) => {
    const response = await request.get('/api/test/headers');

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.headers.authorization).toBe(false);
  });
});
