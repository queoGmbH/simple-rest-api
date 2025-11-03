import { test, expect } from '@playwright/test';

test.describe('Basic API Endpoints', () => {
  test('GET /api/test/hello returns success', async ({ request }) => {
    const response = await request.get('/api/test/hello');

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('application/json');

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.message).toBe('Hello from Simple REST API!');
    expect(data.timestamp).toBeGreaterThan(0);
  });

  test('GET /api/test/echo/{message} echoes the message', async ({ request }) => {
    const testMessage = 'HelloWorld123';
    const response = await request.get(`/api/test/echo/${testMessage}`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.message).toBe(testMessage);
    expect(data.length).toBe(testMessage.length);
  });

  test('GET /api/test/echo with special characters', async ({ request }) => {
    const testMessage = 'test-message_123';
    const response = await request.get(`/api/test/echo/${testMessage}`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.message).toBe(testMessage);
  });
});
