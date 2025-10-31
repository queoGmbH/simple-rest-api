.. include:: /Includes.rst.txt

.. _events:

======
Events
======

The extension provides PSR-14 events that allow you to customize and extend
the API behavior at different stages of request processing.

Event Overview
==============

The following events are dispatched during API request handling:

1. **BeforeParameterMappingEvent** - Before URL parameters are extracted
2. **AfterParameterMappingEvent** - After parameters are extracted but before endpoint invocation
3. **ModifyApiResponseEvent** - After endpoint execution, before sending response to client

Event Lifecycle
===============

Here's the complete flow of events during an API request:

.. code-block:: text

   1. Request arrives at ApiResolverMiddleware
   2. Endpoint is resolved
   3. ➡️  BeforeParameterMappingEvent is dispatched
   4. Parameters are extracted from URL
   5. Parameters are typed (string → int, etc.)
   6. ➡️  AfterParameterMappingEvent is dispatched
   7. Endpoint method is invoked
   8. Method returns ResponseInterface
   9. ➡️  ModifyApiResponseEvent is dispatched
   10. Response is returned to client

BeforeParameterMappingEvent
============================

Location: ``Classes/Event/BeforeParameterMappingEvent.php``

**Dispatched:** Before URL parameters are extracted and mapped to method arguments.

This event allows you to modify the raw parameters before they are processed.

Event API
---------

.. code-block:: php

   final class BeforeParameterMappingEvent
   {
       public function getPathParameters(): Parameters
       public function getApiEndpoint(): ApiEndpoint
       public function getApiRequest(): ApiRequest
       public function overrideParameters(Parameters $pathParameters): void
   }

Use Cases
---------

* Normalize or sanitize URL parameters
* Add default values for missing parameters
* Transform parameter format before type conversion
* Validate parameter format
* Implement custom parameter extraction logic

Example: Adding Default Parameters
-----------------------------------

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
   use Queo\SimpleRestApi\Collection\Parameters;

   final readonly class DefaultParameterListener
   {
       public function addDefaults(BeforeParameterMappingEvent $event): void
       {
           $parameters = $event->getPathParameters();
           $endpoint = $event->getApiEndpoint();

           // Add default 'limit' parameter for list endpoints
           if (str_contains($endpoint->path, '/list')) {
               // Access and modify the raw parameter array
               $rawParams = $parameters->getParameterArray();
               if (!isset($rawParams['limit'])) {
                   $rawParams['limit'] = '10'; // String value
               }

               // Create new Parameters object with modified values
               $newParameters = new Parameters(
                   $endpoint->parameterList,
                   $rawParams,
                   $event->getApiRequest()->getRequest()
               );

               $event->overrideParameters($newParameters);
           }
       }
   }

Register in ``Configuration/Services.yaml``:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\DefaultParameterListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/default-parameters'
           event: Queo\SimpleRestApi\Event\BeforeParameterMappingEvent
           method: 'addDefaults'

Example: Parameter Normalization
---------------------------------

.. code-block:: php

   public function normalizeParameters(BeforeParameterMappingEvent $event): void
   {
       $parameters = $event->getPathParameters();
       $rawParams = $parameters->getParameterArray();

       // Convert all parameters to lowercase
       $normalizedParams = array_map('strtolower', $rawParams);

       $newParameters = new Parameters(
           $event->getApiEndpoint()->parameterList,
           $normalizedParams,
           $event->getApiRequest()->getRequest()
       );

       $event->overrideParameters($newParameters);
   }

AfterParameterMappingEvent
===========================

Location: ``Classes/Event/AfterParameterMappingEvent.php``

**Dispatched:** After parameters are mapped to method arguments but before the endpoint method is invoked.

This event allows you to modify the final typed parameters that will be passed to your endpoint method.

Event API
---------

.. code-block:: php

   final class AfterParameterMappingEvent
   {
       /** @return mixed[] */
       public function getMethodParameters(): array

       public function getEndpoint(): ApiEndpoint
       public function getApiRequest(): ApiRequest

       /** @param mixed[] $methodParameters */
       public function overrideMethodParameters(array $methodParameters): void
   }

Use Cases
---------

* Modify typed parameters before method invocation
* Transform simple parameters into domain objects (e.g., ID → Model)
* Fetch and inject Extbase models from repositories
* Transform parameter values based on business logic
* Implement custom parameter validation
* Override parameters conditionally

Example: Loading Extbase Models
--------------------------------

Transform an integer ID parameter into a full Extbase domain model:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use MyVendor\MyExtension\Domain\Repository\UserRepository;
   use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
   use TYPO3\CMS\Core\Http\JsonResponse;

   final readonly class ModelLoaderListener
   {
       public function __construct(
           private UserRepository $userRepository
       ) {}

       public function loadUserModel(AfterParameterMappingEvent $event): void
       {
           $endpoint = $event->getEndpoint();
           $parameters = $event->getMethodParameters();

           // Only process user endpoints
           if (!str_contains($endpoint->path, '/users/')) {
               return;
           }

           // Check if first parameter is a user ID
           if (isset($parameters[0]) && is_int($parameters[0])) {
               $userId = $parameters[0];
               $user = $this->userRepository->findByUid($userId);

               if ($user === null) {
                   // Could throw exception or handle differently
                   return;
               }

               // Replace the ID with the actual User model
               $parameters[0] = $user;
               $event->overrideMethodParameters($parameters);
           }
       }
   }

Now your endpoint method receives the User model directly:

.. code-block:: php

   use MyVendor\MyExtension\Domain\Model\User;

   #[AsApiEndpoint(method: 'GET', path: '/v1/users/{userId}')]
   public function getUser(User $user): ResponseInterface
   {
       // Receive User model directly instead of ID!
       return new JsonResponse([
           'id' => $user->getUid(),
           'name' => $user->getName(),
           'email' => $user->getEmail(),
       ]);
   }

Register in ``Configuration/Services.yaml``:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\ModelLoaderListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/model-loader'
           event: Queo\SimpleRestApi\Event\AfterParameterMappingEvent
           method: 'loadUserModel'

Example: Parameter Validation
------------------------------

.. code-block:: php

   use Psr\Http\Message\ResponseInterface;
   use TYPO3\CMS\Core\Http\JsonResponse;

   public function validateParameters(AfterParameterMappingEvent $event): void
   {
       $parameters = $event->getMethodParameters();
       $endpoint = $event->getEndpoint();

       // Validate numeric parameters are in valid range
       foreach ($parameters as $index => $param) {
           if (is_int($param) && $param < 1) {
               // Could throw exception or modify parameter
               $parameters[$index] = 1; // Set minimum value
           }
       }

       $event->overrideMethodParameters($parameters);
   }

ModifyApiResponseEvent
======================

Location: ``Classes/Event/ModifyApiResponseEvent.php``

**Dispatched:** After the API endpoint method has been invoked and returned a response,
but before the response is sent to the client.

This event allows you to modify the response, add headers, change status codes,
or perform any other response modifications.

Event API
---------

.. code-block:: php

   final class ModifyApiResponseEvent
   {
       public function getResponse(): ResponseInterface
       public function setResponse(ResponseInterface $response): void
       public function getEndpoint(): ApiEndpoint
       public function getApiRequest(): ApiRequestInterface
   }

Use Cases
---------

* Adding CORS headers to all API responses
* Adding custom headers (X-API-Version, X-Request-ID, etc.)
* Adding caching headers based on HTTP method or endpoint
* Logging response details
* Modifying response content or status codes
* Implementing response transformations
* Adding security headers

Example: Adding CORS Headers
-----------------------------

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

           // Add CORS headers to allow cross-origin requests
           $response = $response
               ->withHeader('Access-Control-Allow-Origin', '*')
               ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
               ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

           $event->setResponse($response);
       }
   }

Register in ``Configuration/Services.yaml``:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\CorsHeaderListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/cors-headers'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'addCorsHeaders'

Example: Conditional Caching Headers
-------------------------------------

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

Example: Adding Request Tracking
---------------------------------

.. code-block:: php

   public function addRequestTracking(ModifyApiResponseEvent $event): void
   {
       $response = $event->getResponse();

       // Add unique request ID for tracking
       $requestId = uniqid('req_', true);
       $response = $response->withHeader('X-Request-ID', $requestId);

       // Add API version
       $response = $response->withHeader('X-API-Version', '1.0');

       $event->setResponse($response);
   }

Example: Response Logging
--------------------------

.. code-block:: php

   use Psr\Log\LoggerInterface;

   final readonly class ResponseLoggerListener
   {
       public function __construct(
           private LoggerInterface $logger
       ) {}

       public function logResponse(ModifyApiResponseEvent $event): void
       {
           $endpoint = $event->getEndpoint();
           $response = $event->getResponse();

           $this->logger->info('API Response', [
               'method' => $endpoint->httpMethod,
               'path' => $endpoint->path,
               'status' => $response->getStatusCode(),
               'request_id' => $response->getHeader('X-Request-ID')[0] ?? null,
           ]);
       }
   }

Example: Complete Response Replacement
---------------------------------------

.. code-block:: php

   use TYPO3\CMS\Core\Http\JsonResponse;

   public function modifyResponse(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();
       $response = $event->getResponse();

       // Wrap all responses in standard envelope
       $body = json_decode($response->getBody()->getContents(), true);

       $wrappedResponse = new JsonResponse([
           'success' => $response->getStatusCode() < 400,
           'data' => $body,
           'timestamp' => time(),
           'endpoint' => $endpoint->path,
       ], $response->getStatusCode());

       // Preserve original headers
       foreach ($response->getHeaders() as $name => $values) {
           foreach ($values as $value) {
               $wrappedResponse = $wrappedResponse->withAddedHeader($name, $value);
           }
       }

       $event->setResponse($wrappedResponse);
   }

Complete Example: API Response Modifier
========================================

Here's a complete example that combines multiple response modifications:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Psr\Log\LoggerInterface;
   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;

   final readonly class CompleteApiResponseModifier
   {
       public function __construct(
           private LoggerInterface $logger
       ) {}

       public function modifyResponse(ModifyApiResponseEvent $event): void
       {
           $response = $event->getResponse();
           $endpoint = $event->getEndpoint();

           // 1. Add CORS headers
           $response = $response
               ->withHeader('Access-Control-Allow-Origin', '*')
               ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
               ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

           // 2. Add API version and request tracking
           $requestId = uniqid('req_', true);
           $response = $response
               ->withHeader('X-API-Version', '1.0')
               ->withHeader('X-Request-ID', $requestId);

           // 3. Add caching headers based on HTTP method
           if ($endpoint->httpMethod === 'GET') {
               $response = $response
                   ->withHeader('Cache-Control', 'public, max-age=300');
           } else {
               $response = $response
                   ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
           }

           // 4. Log the response
           $this->logger->info('API Response', [
               'method' => $endpoint->httpMethod,
               'path' => $endpoint->path,
               'status' => $response->getStatusCode(),
               'request_id' => $requestId,
           ]);

           $event->setResponse($response);
       }
   }

Register in ``Configuration/Services.yaml``:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\CompleteApiResponseModifier:
       tags:
         - name: event.listener
           identifier: 'my-extension/complete-api-response-modifier'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'modifyResponse'

Best Practices
==============

Event Listener Guidelines
--------------------------

1. **Keep listeners focused** - Each listener should do one thing well
2. **Use dependency injection** - Inject services via constructor
3. **Consider performance** - Events fire on every API request
4. **Don't throw exceptions** - Handle errors gracefully
5. **Document your listeners** - Explain what they do and why
6. **Use specific event methods** - Name methods descriptively (not just ``__invoke``)
7. **Test your listeners** - Write unit tests for event listeners

Multiple Listeners
------------------

You can register multiple listeners for the same event:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\CorsHeaderListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/cors'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'addCorsHeaders'

     MyVendor\MyExtension\EventListener\CachingHeaderListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/caching'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'addCachingHeaders'

     MyVendor\MyExtension\EventListener\ResponseLoggerListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/logging'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'logResponse'

Listeners are executed in the order they are registered.

Conditional Event Handling
---------------------------

You can conditionally handle events based on endpoint path or other criteria:

.. code-block:: php

   public function handleEvent(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();

       // Only handle specific endpoints
       if (!str_starts_with($endpoint->path, '/v1/admin/')) {
           return;
       }

       // Your logic here
   }

Event Listener Priority
------------------------

TYPO3 supports listener priorities. Lower numbers execute first:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\HighPriorityListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/high-priority'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'handleEvent'
           before: 'my-extension/low-priority'

     MyVendor\MyExtension\EventListener\LowPriorityListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/low-priority'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'handleEvent'
           after: 'my-extension/high-priority'

Testing Event Listeners
========================

Unit Test Example
-----------------

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\Tests\Unit\EventListener;

   use MyVendor\MyExtension\EventListener\CorsHeaderListener;
   use PHPUnit\Framework\TestCase;
   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
   use Queo\SimpleRestApi\Http\ApiRequestInterface;
   use Queo\SimpleRestApi\Value\ApiEndpoint;
   use TYPO3\CMS\Core\Http\JsonResponse;

   final class CorsHeaderListenerTest extends TestCase
   {
       public function testAddsCorsHeaders(): void
       {
           $listener = new CorsHeaderListener();

           $response = new JsonResponse(['data' => 'test']);
           $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
           $apiRequest = $this->createMock(ApiRequestInterface::class);

           $event = new ModifyApiResponseEvent($response, $endpoint, $apiRequest);

           $listener->addCorsHeaders($event);

           $modifiedResponse = $event->getResponse();

           $this->assertTrue($modifiedResponse->hasHeader('Access-Control-Allow-Origin'));
           $this->assertEquals(['*'], $modifiedResponse->getHeader('Access-Control-Allow-Origin'));
       }
   }

See Also
========

* :ref:`usage` - Basic endpoint usage
* :ref:`developer` - Advanced development topics
* Example listeners: ``Classes/EventListener/ApiResponseModifierExample.php``
