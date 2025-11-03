import { test, expect } from '@playwright/test';

test.describe('HTTP Methods', () => {
  test('POST /api/test/users creates a user', async ({ request }) => {
    const response = await request.post('/api/test/users', {
      data: {
        name: 'John Doe',
        email: 'john.doe@example.com'
      }
    });

    expect(response.status()).toBe(201);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.user.name).toBe('John Doe');
    expect(data.user.email).toBe('john.doe@example.com');
    expect(data.user.id).toBeGreaterThan(0);
    expect(data.user.created_at).toBeTruthy();
  });

  test('POST /api/test/users validates required fields', async ({ request }) => {
    const response = await request.post('/api/test/users', {
      data: {
        name: ''
      }
    });

    expect(response.status()).toBe(400);

    const data = await response.json();
    expect(data.success).toBe(false);
    expect(data.errors).toContain('Name is required');
    expect(data.errors).toContain('Email is required');
  });

  test('PUT /api/test/users/{id} updates a user', async ({ request }) => {
    const userId = 123;
    const response = await request.put(`/api/test/users/${userId}`, {
      data: {
        name: 'Jane Doe',
        email: 'jane.doe@example.com'
      }
    });

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.user.id).toBe(userId);
    expect(data.user.name).toBe('Jane Doe');
    expect(data.user.email).toBe('jane.doe@example.com');
    expect(data.user.updated_at).toBeTruthy();
  });

  test('PATCH /api/test/users/{id} partially updates a user', async ({ request }) => {
    const userId = 456;
    const response = await request.patch(`/api/test/users/${userId}`, {
      data: {
        name: 'Updated Name'
      }
    });

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.id).toBe(userId);
    expect(data.updated_fields).toContain('name');
    expect(data.data.name).toBe('Updated Name');
  });

  test('DELETE /api/test/users/{id} deletes a user', async ({ request }) => {
    const userId = 789;
    const response = await request.delete(`/api/test/users/${userId}`);

    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.message).toBe('User deleted successfully');
    expect(data.deleted_id).toBe(userId);
  });

  test('DELETE /api/test/users/999 returns 404 for non-existent user', async ({ request }) => {
    const response = await request.delete('/api/test/users/999');

    expect(response.status()).toBe(404);

    const data = await response.json();
    expect(data.success).toBe(false);
    expect(data.error).toBe('User not found');
  });
});
