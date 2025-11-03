import { test, expect } from '@playwright/test';

test.describe('Parameter Type Handling', () => {
  test('GET /api/test/types handles different parameter types', async ({ request }) => {
    const response = await request.get('/api/test/types/42/hello');

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.intParam).toBe(42);
    expect(data.stringParam).toBe('hello');
    expect(data.types.intParam).toBe('integer');
    expect(data.types.stringParam).toBe('string');
  });

  test('GET /api/test/types with string for int parameter', async ({ request }) => {
    const response = await request.get('/api/test/types/999/test-string');

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.intParam).toBe(999);
    expect(data.stringParam).toBe('test-string');
  });
});
