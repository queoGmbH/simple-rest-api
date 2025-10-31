# Simple REST API for TYPO3

[![TYPO3](https://img.shields.io/badge/TYPO3-13.4+-orange.svg?style=flat-square)](https://get.typo3.org/version/13)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg?style=flat-square)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green.svg?style=flat-square)](LICENSE)
[![Latest Release](https://img.shields.io/badge/release-0.2.0--rc1-blue.svg?style=flat-square)](https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/-/tags)

A TYPO3 extension that provides a simple REST API framework to create endpoints via PHP attributes. It focuses on simplicity - handling routing and simple parameters from the URL, while leaving everything else to the developer.

## ✨ Features

- 🎯 **Simple endpoint configuration** via `#[AsApiEndpoint]` PHP attribute
- 🔗 **URL parameter mapping** to method arguments (int, string, float, bool)
- 📥 **ServerRequest integration** for accessing request body, headers, and query parameters
- 🎨 **Backend module** to view all registered API endpoints with documentation
- 📝 **Parameter documentation** with automatic type extraction from PHPDoc
- 🔄 **PSR-14 events** to adjust parameters before reaching your endpoint
- 🚀 **Zero configuration** - endpoints are auto-discovered via dependency injection

## 📦 Installation

```bash
composer require queo/simple-rest-api
```

Activate the extension:

```bash
vendor/bin/typo3 extension:activate simple_rest_api
```

## 🚀 Quick Start

### 1. Configure Route Enhancer

Add to your site configuration (`config/sites/<site>/config.yaml`):

```yaml
imports:
  - { resource: "EXT:simple_rest_api/Configuration/Yaml/RouteEnhancer.yaml" }
```

Or configure manually:

```yaml
routeEnhancers:
  SimpleRestApiEnhancer:
    type: SimpleRestApiEnhancer
```

### 2. Create Your First Endpoint

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Controller;

use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

final class ApiController
{
    #[AsApiEndpoint(
        method: 'GET',
        path: '/v1/hello',
        summary: 'Simple hello world endpoint',
        description: 'Returns a friendly greeting'
    )]
    public function hello(): ResponseInterface
    {
        return new JsonResponse(['message' => 'Hello, World!']);
    }
}
```

### 3. Clear Cache & Test

```bash
vendor/bin/typo3 cache:flush
```

Your endpoint is now accessible at: `https://your-domain.com/api/v1/hello`

## 📖 Documentation

For comprehensive documentation including:
- Complete usage examples
- URL parameters handling
- POST/PUT/PATCH/DELETE requests
- Combining URL parameters with ServerRequest
- Error handling and validation
- Architecture and contribution guide
- Troubleshooting

**See: [Documentation/Index.rst](Documentation/Index.rst)**

Or build the docs locally:

```bash
pip install sphinx sphinx-rtd-theme
sphinx-build Documentation Documentation/_build
```

## 💡 Common Use Cases

### URL Parameters

```php
/**
 * @param int $userId The ID of the user
 */
#[AsApiEndpoint(method: 'GET', path: '/v1/users/{userId}')]
public function getUser(int $userId): ResponseInterface
{
    return new JsonResponse(['userId' => $userId]);
}
```

**Access:** `/api/v1/users/123`

### POST with Request Body

```php
use Psr\Http\Message\ServerRequestInterface;

#[AsApiEndpoint(method: 'POST', path: '/v1/users')]
public function createUser(ServerRequestInterface $request): ResponseInterface
{
    $body = json_decode($request->getBody()->getContents(), true);

    return new JsonResponse([
        'name' => $body['name'] ?? '',
        'email' => $body['email'] ?? ''
    ], 201);
}
```

### Combining URL Parameters with Request Body

```php
/**
 * @param int $userId The ID of the user to update
 */
#[AsApiEndpoint(method: 'PATCH', path: '/v1/users/{userId}')]
public function updateUser(
    int $userId,
    ServerRequestInterface $request
): ResponseInterface {
    $body = json_decode($request->getBody()->getContents(), true);

    // Update user logic here

    return new JsonResponse(['id' => $userId, 'updated' => true]);
}
```

## 🎯 Backend Module

View all registered API endpoints in the TYPO3 backend:

**Site** → **REST API Endpoints**

The module displays:
- HTTP method and endpoint path
- Summary and description
- Parameter details with types and descriptions

## 🏗️ Architecture

### Core Components

- **AsApiEndpoint** - PHP attribute to mark methods as endpoints
- **ApiResolverMiddleware** - Main middleware handling endpoint resolution
- **ApiEndpointProvider** - Manages endpoint registration and discovery
- **Route Enhancer** - Custom TYPO3 route enhancer for `/api/*` paths
- **Events** - BeforeParameterMappingEvent, AfterParameterMappingEvent

### Philosophy

This extension intentionally keeps things simple:
- ✅ Handles routing and parameter mapping
- ✅ Integrates cleanly with TYPO3's routing system
- ❌ Does NOT handle serialization/deserialization
- ❌ Does NOT handle authentication (use TYPO3's built-in mechanisms)
- ❌ Does NOT handle validation (implement in your endpoints)

This keeps the extension maintainable and flexible for your specific use cases.

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch from `main`
3. Write tests for your changes
4. Ensure all code quality checks pass (PHPStan, PHPCS, Rector)
5. Create a merge request

See [Documentation/Developer/Index.rst](Documentation/Developer/Index.rst) for detailed contribution guidelines.

## 📜 License

This extension is licensed under GPL-2.0-or-later.

## 🔗 Links

- **Repository:** https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api
- **Issues:** https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/-/issues
- **Documentation:** [Documentation/Index.rst](Documentation/Index.rst)

## 👥 Credits

Developed and maintained by [Queo Group](https://www.queo.de/)

Author: Sebastian Hofer
