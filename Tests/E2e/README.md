# E2E Tests with Playwright

End-to-end tests for the Simple REST API TYPO3 extension using Playwright.

## Overview

These tests verify the complete request/response cycle by making actual HTTP calls to API endpoints in a real TYPO3 environment running in Docker.

## Prerequisites

- Docker and Docker Compose
- Node.js 18+ and npm

## Running Tests Locally

### Quick Start

```bash
# Install dependencies
cd Tests/E2e
npm install

# Start TYPO3 environment and run tests
npm test
```

The `playwright.config.ts` is configured to automatically start the Docker environment via `webServer` configuration.

### Manual Setup

If you prefer to manage the Docker environment manually:

```bash
# Start TYPO3 environment
docker-compose -f ../../docker/docker-compose.e2e.yml up -d

# Wait for TYPO3 to be ready (check health)
docker-compose -f ../../docker/docker-compose.e2e.yml ps

# Run tests
cd Tests/E2e
BASE_URL=http://localhost:8080 npm test

# Stop environment
docker-compose -f ../../docker/docker-compose.e2e.yml down
```

## Test Structure

```
Tests/E2e/
├── tests/
│   ├── api-basic.spec.ts           # Basic GET endpoints
│   ├── http-methods.spec.ts        # POST, PUT, PATCH, DELETE
│   ├── query-params-headers.spec.ts # Query params and headers
│   └── parameter-types.spec.ts     # Type handling
├── playwright.config.ts             # Playwright configuration
├── package.json                     # Dependencies
└── README.md                        # This file
```

## Test Endpoints

The tests use endpoints from `Tests/Fixtures/TestController.php`:

- `GET /api/test/hello` - Basic health check
- `GET /api/test/echo/{message}` - Echo message back
- `POST /api/test/users` - Create user with validation
- `PUT /api/test/users/{id}` - Update user
- `PATCH /api/test/users/{id}` - Partial update
- `DELETE /api/test/users/{id}` - Delete user
- `GET /api/test/search` - Query parameters test
- `GET /api/test/headers` - Headers test
- `GET /api/test/types/{int}/{string}` - Parameter types test

## CI/CD

Tests run automatically in GitLab CI in the `e2e` stage after unit tests pass.

## Development

### Debug Mode

```bash
npm run test:debug
```

### UI Mode

```bash
npm run test:ui
```

### Run Specific Test

```bash
npx playwright test api-basic.spec.ts
```

### View Test Report

```bash
npm run report
```

## Troubleshooting

### TYPO3 Not Starting

Check Docker logs:
```bash
docker-compose -f ../../docker/docker-compose.e2e.yml logs typo3
```

### Tests Failing

1. Verify TYPO3 is accessible: `curl http://localhost:8080/api/test/hello`
2. Check site configuration in container
3. Verify route enhancer is loaded
4. Check extension is activated

### Clean Start

```bash
docker-compose -f ../../docker/docker-compose.e2e.yml down -v
docker-compose -f ../../docker/docker-compose.e2e.yml up -d --build
```
