.. include:: /Includes.rst.txt

.. _developer:

================
Developer Guide
================

This chapter covers advanced topics and internal architecture for developers
who want to extend or contribute to the Simple REST API extension.

Architecture Overview
=====================

The extension follows a layered architecture:

.. code-block:: text

   Request → Middleware → Route Enhancer → Endpoint Provider → Controller Method
                ↓              ↓                  ↓
           Aspect         Path Mapping      Parameter Extraction

Core Components
===============

AsApiEndpoint Attribute
-----------------------

Location: `Classes/Attribute/AsApiEndpoint.php`

PHP 8 attribute that marks methods as API endpoints:

.. code-block:: php

   #[\Attribute(\Attribute::TARGET_METHOD)]
   final readonly class AsApiEndpoint
   {
       public const TAG_NAME = 'simple_rest_api.endpoint';

       public function __construct(
           public string $method,
           public string $path,
           public string $summary = '',
           public string $description = ''
       ) {}
   }

ApiEndpointProvider
-------------------

Location: `Classes/Provider/ApiEndpointProvider.php`

Manages all registered API endpoints:

.. code-block:: php

   final class ApiEndpointProvider
   {
       private array $endpoints = [];

       public function addEndpoint(
           string $className,
           string $methodName,
           string $httpMethod,
           string $path,
           string $summary = '',
           string $description = ''
       ): void

       public function getEndpoint(ApiRequestInterface $apiRequest): ?ApiEndpoint

       public function getAllEndpoints(): array
   }

Key features:

* Stores endpoint metadata
* Matches incoming requests to endpoints
* Extracts parameter information via reflection
* Parses PHPDoc for parameter descriptions

ApiEndpoint Value Object
-------------------------

Location: `Classes/Value/ApiEndpoint.php`

Represents a single API endpoint:

.. code-block:: php

   final readonly class ApiEndpoint
   {
       public function __construct(
           public string $className,
           public string $method,
           public string $path,
           public string $httpMethod,
           public array $parameterList,
           public string $summary = '',
           public string $description = '',
           public array $parameters = []
       ) {}
   }

Middleware Stack
================

ApiResolverMiddleware
---------------------

Location: `Classes/Middleware/ApiResolverMiddleware.php`

Main middleware that:

1. Detects API requests (checks for SimpleRestApiAspect)
2. Resolves the endpoint via ApiEndpointProvider
3. Extracts parameters from URL
4. Invokes the endpoint method
5. Returns the response

Registration in `Configuration/RequestMiddlewares.php`:

.. code-block:: php

   'frontend' => [
       'simple-rest-api/api-resolver' => [
           'target' => ApiResolverMiddleware::class,
           'after' => [
               'typo3/cms-frontend/page-resolver',
           ],
       ],
   ]

ApiAspectMiddleware
-------------------

Location: `Classes/Middleware/ApiAspectMiddleware.php`

Sets up the SimpleRestApiAspect context for API requests.

CacheHashFixer
--------------

Location: `Classes/Middleware/CacheHashFixer.php`

Handles cache hash adjustments for API requests to prevent caching issues.

Route Enhancer
==============

SimpleRestApiEnhancer
---------------------

Location: `Classes/Routing/Enhancer/SimpleRestApiEnhancer.php`

Custom TYPO3 route enhancer that:

* Matches `/api/*` paths
* Creates route candidates for API requests
* Integrates with TYPO3's routing system

Dependency Injection
====================

ApiEndpointProviderPass
-----------------------

Location: `Classes/DependencyInjection/ApiEndpointProviderPass.php`

Compiler pass that:

1. Finds all services tagged with `AsApiEndpoint::TAG_NAME`
2. Extracts attribute metadata
3. Registers endpoints with ApiEndpointProvider

.. code-block:: php

   final class ApiEndpointProviderPass implements CompilerPassInterface
   {
       public function process(ContainerBuilder $container): void
       {
           $taggedServices = $container->findTaggedServiceIds(
               AsApiEndpoint::TAG_NAME
           );

           foreach ($taggedServices as $serviceId => $tags) {
               // Extract and register endpoint metadata
           }
       }
   }

Services Configuration
----------------------

Location: `Configuration/Services.yaml`

Configures service autowiring and autoconfiguration:

.. code-block:: yaml

   services:
     _defaults:
       autowire: true
       autoconfigure: true
       public: false

     Queo\SimpleRestApi\:
       resource: '../Classes/*'
       exclude:
         - '../Classes/Domain/Model/*'
         - '../Classes/Attribute/*'

Parameter Extraction
====================

Reflection-Based Extraction
----------------------------

The extension uses PHP reflection to extract parameter information:

.. code-block:: php

   private function extractParameterInformation(
       string $className,
       string $methodName,
       array $parameterNames
   ): array {
       $reflectionClass = new ReflectionClass($className);
       $reflectionMethod = $reflectionClass->getMethod($methodName);

       // Get PHPDoc
       $docComment = $reflectionMethod->getDocComment();
       $paramDescriptions = $this->parseParamDescriptions($docComment ?: '');

       // Get method parameters
       $methodParameters = $reflectionMethod->getParameters();

       foreach ($methodParameters as $reflectionParameter) {
           // Extract type, description, etc.
       }
   }

PHPDoc Parsing
--------------

Parameter descriptions are extracted from PHPDoc blocks:

.. code-block:: php

   private function parseParamDescriptions(string $docComment): array
   {
       $descriptions = [];

       if (preg_match_all(
           '/@param\s+(\S+)\s+\$(\w+)\s+(.*)$/m',
           $docComment,
           $matches,
           PREG_SET_ORDER
       )) {
           foreach ($matches as $match) {
               $paramName = $match[2];
               $description = trim($match[3]);
               $descriptions[$paramName] = $description;
           }
       }

       return $descriptions;
   }

Events
======

The extension provides PSR-14 events for customization:

ModifyApiResponseEvent
----------------------

Location: `Classes/Event/ModifyApiResponseEvent.php`

**Fired after the API endpoint method has been invoked and before the response is returned to the client.**

This event allows you to modify the response, add headers, change status codes, or perform any other response modifications.

.. code-block:: php

   final class ModifyApiResponseEvent
   {
       public function __construct(
           private ResponseInterface $response,
           private readonly ApiEndpoint $endpoint,
           private readonly ApiRequestInterface $apiRequest
       ) {}

       public function getResponse(): ResponseInterface
       public function setResponse(ResponseInterface $response): void
       public function getEndpoint(): ApiEndpoint
       public function getApiRequest(): ApiRequestInterface
   }

**Common Use Cases:**

* Adding CORS headers to all API responses
* Adding custom headers (X-API-Version, X-Request-ID, etc.)
* Adding caching headers based on HTTP method or endpoint
* Logging response details
* Modifying response content or status codes

**Example: Adding CORS Headers**

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;

   final readonly class CorsHeaderListener
   {
       public function addCorsHeaders(ModifyApiResponseEvent $event): void
       {
           $response = $event->getResponse();

           // Add CORS headers
           $response = $response
               ->withHeader('Access-Control-Allow-Origin', '*')
               ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
               ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

           $event->setResponse($response);
       }
   }

Register in `Configuration/Services.yaml`:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\CorsHeaderListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/cors-headers'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'addCorsHeaders'

**Example: Conditional Caching Headers**

.. code-block:: php

   public function addCachingHeaders(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();
       $response = $event->getResponse();

       // Only cache GET requests
       if ($endpoint->httpMethod === 'GET') {
           $response = $response
               ->withHeader('Cache-Control', 'public, max-age=300')
               ->withHeader('Expires', gmdate('D, d M Y H:i:s \\G\\M\\T', time() + 300));
       } else {
           // Don't cache POST, PUT, PATCH, DELETE
           $response = $response
               ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
               ->withHeader('Pragma', 'no-cache')
               ->withHeader('Expires', '0');
       }

       $event->setResponse($response);
   }

**Example: Adding Request Tracking**

.. code-block:: php

   public function addRequestTracking(ModifyApiResponseEvent $event): void
   {
       $response = $event->getResponse();

       // Add unique request ID
       $requestId = uniqid('req_', true);
       $response = $response->withHeader('X-Request-ID', $requestId);

       // Add API version
       $response = $response->withHeader('X-API-Version', '1.0');

       $event->setResponse($response);
   }

See the included example: `Classes/EventListener/ApiResponseModifierExample.php`

BeforeParameterMappingEvent
---------------------------

Location: `Classes/Event/BeforeParameterMappingEvent.php`

Fired before parameters are mapped from the URL to method arguments.

.. code-block:: php

   final class BeforeParameterMappingEvent
   {
       public function __construct(
           private ApiRequest $apiRequest,
           private array $parameters
       ) {}

       public function getApiRequest(): ApiRequest
       public function getParameters(): array
       public function setParameters(array $parameters): void
   }

AfterParameterMappingEvent
--------------------------

Location: `Classes/Event/AfterParameterMappingEvent.php`

Fired after parameters are mapped.

Event Listener Example
----------------------

.. code-block:: php

   final class ParameterMappingListener
   {
       public function __invoke(BeforeParameterMappingEvent $event): void
       {
           $parameters = $event->getParameters();

           // Modify parameters
           $parameters['modified'] = true;

           $event->setParameters($parameters);
       }
   }

Register in `Configuration/Services.yaml`:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\ParameterMappingListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/parameter-mapping'
           event: Queo\SimpleRestApi\Event\BeforeParameterMappingEvent

Backend Module
==============

EndpointListController
----------------------

Location: `Classes/Controller/Backend/EndpointListController.php`

Backend controller that displays all registered endpoints:

.. code-block:: php

   final class EndpointListController
   {
       public function __construct(
           private readonly ApiEndpointProvider $apiEndpointProvider,
           private readonly ModuleTemplateFactory $moduleTemplateFactory
       ) {}

       public function listAction(ServerRequestInterface $request): ResponseInterface
       {
           $endpoints = $this->apiEndpointProvider->getAllEndpoints();
           // Render template
       }
   }

Module Configuration
--------------------

Location: `Configuration/Backend/Modules.php`

.. code-block:: php

   return [
       'site_simplerestapi' => [
           'parent' => 'site',
           'position' => ['after' => 'site_redirects'],
           'access' => 'admin',
           'path' => '/module/site/simple-rest-api',
           'labels' => 'LLL:EXT:simple_rest_api/Resources/Private/Language/locallang_mod.xlf',
           'controllerActions' => [
               EndpointListController::class => ['list'],
           ],
       ],
   ];

Template
--------

Location: `Resources/Private/Templates/Backend/EndpointList/List.html`

Fluid template using Bootstrap 5 accordion components.

Testing
=======

Unit Tests
----------

Location: `Tests/Unit/`

Example test:

.. code-block:: php

   #[CoversClass(ApiEndpointProvider::class)]
   final class ApiEndpointProviderTest extends UnitTestCase
   {
       #[Test]
       public function finds_endpoint_from_api_request(): void
       {
           $apiEndpointProvider = new ApiEndpointProvider();
           $apiEndpointProvider->addEndpoint(
               stdClass::class,
               'myEndpoint',
               'GET',
               '/v1/my-api-endpoint'
           );

           // Test endpoint resolution
       }
   }

Running Tests
-------------

.. code-block:: bash

   # Unit tests
   .Build/bin/phpunit -c phpunit.xml

   # Integration tests
   .Build/bin/phpunit -c phpunit-integration.xml

Code Quality
============

PHPStan
-------

Static analysis at level 9:

.. code-block:: bash

   .Build/bin/phpstan analyse

Configuration: `phpstan.neon`

PHP_CodeSniffer
---------------

.. code-block:: bash

   # Check
   .Build/bin/phpcs

   # Fix
   .Build/bin/phpcbf

Rector
------

Automated refactoring:

.. code-block:: bash

   .Build/bin/rector process

Configuration: `rector.php`

GrumPHP
-------

Pre-commit hooks:

.. code-block:: bash

   .Build/vendor/bin/grumphp run

Contributing
============

To contribute to the extension:

1. **Fork the repository** on GitLab
2. **Create a feature branch** from `main`
3. **Write tests** for your changes
4. **Ensure code quality** - all checks must pass
5. **Create a merge request** with a clear description
6. **Follow commit message conventions**:

   * `[FEATURE]` - New features
   * `[BUGFIX]` - Bug fixes
   * `[TASK]` - General tasks
   * `[DOCS]` - Documentation changes

Extension Development Setup
===========================

Using DDEV
----------

.. code-block:: bash

   # Clone repository
   git clone <repository-url>
   cd simple-rest-api

   # Install dependencies
   composer install

   # Run tests
   .Build/bin/phpunit -c phpunit.xml

   # Start development
   ddev start

Code Standards
--------------

* PHP 8.2+ features encouraged
* Type hints required for all parameters and return values
* PHPDoc required for complex methods
* Follow PSR-12 coding standards
* Use `final` for classes by default
* Use `readonly` for immutable properties

API Design Guidelines
=====================

When creating endpoints:

1. **Use proper HTTP methods**

   * GET - Retrieve data
   * POST - Create resources
   * PUT - Update entire resource
   * PATCH - Partial update
   * DELETE - Remove resource

2. **Use plural nouns** for collections: `/v1/users`, not `/v1/user`
3. **Use resource nesting** sparingly: `/v1/users/{id}/posts`
4. **Version your API**: Always include `/v1/`, `/v2/` in paths
5. **Return appropriate status codes**
6. **Use consistent response structure**

Debugging
=========

Enable TYPO3 debug mode in `.ddev/config.yaml`:

.. code-block:: yaml

   web_environment:
     - TYPO3_CONTEXT=Development

Check logs:

.. code-block:: bash

   # TYPO3 logs
   tail -f var/log/typo3_*.log

   # Apache logs in DDEV
   ddev logs

Use Xdebug:

.. code-block:: bash

   ddev xdebug on

Performance Considerations
==========================

* **Reflection is cached** - Parameter extraction uses reflection but results are cached
* **Endpoint resolution is optimized** - Fast array lookups by HTTP method and path
* **Minimize dependencies** - Only inject what you need in controllers
* **Use TYPO3 caching** - Leverage TYPO3's caching framework for expensive operations

Security Considerations
=======================

The extension handles routing only. You must implement:

* **Authentication** - Verify user identity
* **Authorization** - Check permissions
* **Input validation** - Validate all user input
* **Output encoding** - Prevent XSS
* **Rate limiting** - Prevent abuse
* **CORS headers** - Control cross-origin access

Example authentication middleware:

.. code-block:: php

   final class ApiAuthMiddleware implements MiddlewareInterface
   {
       public function process(
           ServerRequestInterface $request,
           RequestHandlerInterface $handler
       ): ResponseInterface {
           $authHeader = $request->getHeader('Authorization')[0] ?? '';

           if (!$this->isValidToken($authHeader)) {
               return new JsonResponse(['error' => 'Unauthorized'], 401);
           }

           return $handler->handle($request);
       }
   }

Future Enhancements
===================

Planned features:

* OpenAPI/Swagger documentation generation
* Built-in request validation
* Response serialization helpers
* More events for customization
* CLI command to list endpoints

Contributions welcome!

API Reference
=============

For complete API documentation, see the inline PHPDoc comments in the source code
and the backend module which provides a visual overview of all endpoints.
