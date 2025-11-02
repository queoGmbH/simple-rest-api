.. include:: /Includes.rst.txt

.. _filtering-event-listeners:

==========================================
Filtering Event Listeners by Endpoint
==========================================

When working with PSR-14 events in the Simple REST API extension, you often want
to restrict event listeners to specific endpoints rather than executing for all API requests.

This guide shows different approaches to filter event listeners based on endpoints.

.. contents:: Table of Contents
   :local:
   :depth: 2

The Problem
===========

Without endpoint filtering, your event listeners execute for **every** API request:

.. code-block:: php

   final class CorsHeaderListener
   {
       public function __invoke(ModifyApiResponseEvent $event): void
       {
           // This runs for EVERY API endpoint!
           $response = $event->getResponse();
           $response = $response->withHeader('Access-Control-Allow-Origin', '*');
           $event->setResponse($response);
       }
   }

This can lead to:

* Performance overhead
* Complex conditional logic in listeners
* Hard-to-maintain string comparisons
* Coupling to URL patterns

Solution Overview
=================

The extension provides three complementary approaches:

1. **Helper Methods** - Direct endpoint checking on events (simple cases)
2. **Tags** - Group endpoints by functionality (flexible grouping)
3. **EndpointMatcher Service** - Advanced filtering with wildcards (complex scenarios)

Approach 1: Helper Methods
===========================

All events provide convenient helper methods for checking the current endpoint.

Check by Class and Method
--------------------------

Use type-safe class constants to check if the listener should execute:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
   use MyVendor\MyExtension\Controller\UserController;

   final class UserParameterListener
   {
       public function __invoke(BeforeParameterMappingEvent $event): void
       {
           // Only execute for specific endpoint
           if ($event->isEndpoint(UserController::class, 'getUser')) {
               // Modify parameters only for UserController::getUser
               $params = $event->getPathParameters();
               // ... modify parameters
           }
       }
   }

Check by Path Pattern
---------------------

Match endpoints by their URL path:

.. code-block:: php

   public function __invoke(ModifyApiResponseEvent $event): void
   {
       // Match exact path
       if ($event->matchesPath('/v1/users/{userId}')) {
           // Add custom headers for this specific endpoint
       }
   }

Available Helper Methods
------------------------

All three events provide these methods:

.. code-block:: php

   // Check class and method
   $event->isEndpoint(UserController::class, 'getUser'): bool

   // Check path
   $event->matchesPath('/v1/users/{userId}'): bool

   // Check tags (see next section)
   $event->hasTag('authenticated'): bool
   $event->hasAnyTag(['public', 'cached']): bool
   $event->hasAllTags(['authenticated', 'admin']): bool

Approach 2: Endpoint Tags
==========================

Tags allow you to group endpoints by functionality, making it easy to apply
listeners to multiple related endpoints.

Adding Tags to Endpoints
-------------------------

Add tags when defining endpoints:

.. code-block:: php

   <?php

   namespace MyVendor\MyExtension\Controller;

   use Psr\Http\Message\ResponseInterface;
   use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
   use TYPO3\CMS\Core\Http\JsonResponse;

   final class UserController
   {
       #[AsApiEndpoint(
           method: 'GET',
           path: '/v1/users/{userId}',
           tags: ['authenticated', 'user-management', 'cacheable']
       )]
       public function getUser(int $userId): ResponseInterface
       {
           return new JsonResponse(['user' => $userId]);
       }

       #[AsApiEndpoint(
           method: 'POST',
           path: '/v1/users',
           tags: ['authenticated', 'user-management', 'admin-only']
       )]
       public function createUser(ServerRequestInterface $request): ResponseInterface
       {
           return new JsonResponse(['success' => true]);
       }
   }

Using Tags in Listeners
-----------------------

Check for tags in your event listeners:

.. code-block:: php

   final class AuthenticationListener
   {
       public function __invoke(BeforeParameterMappingEvent $event): void
       {
           // Execute for all endpoints tagged as 'authenticated'
           if ($event->hasTag('authenticated')) {
               // Validate authentication token
               $this->validateAuthentication($event->getApiRequest());
           }
       }
   }

Multiple Tag Conditions
-----------------------

Check for any or all tags:

.. code-block:: php

   // Execute if endpoint has ANY of these tags
   if ($event->hasAnyTag(['public', 'guest-allowed'])) {
       // Allow anonymous access
   }

   // Execute ONLY if endpoint has ALL of these tags
   if ($event->hasAllTags(['authenticated', 'admin-only'])) {
       // Check admin permissions
   }

Common Tag Examples
-------------------

Here are some useful tag categories:

**Access Control:**

* ``authenticated`` - Requires authentication
* ``public`` - Publicly accessible
* ``admin-only`` - Admin access required
* ``guest-allowed`` - Anonymous access allowed

**Caching:**

* ``cacheable`` - Response can be cached
* ``no-cache`` - Never cache response
* ``short-lived`` - Cache for short duration

**Rate Limiting:**

* ``rate-limited`` - Apply rate limiting
* ``unrestricted`` - No rate limits

**Documentation:**

* ``deprecated`` - Endpoint is deprecated
* ``beta`` - Experimental endpoint
* ``stable`` - Production-ready endpoint

Approach 3: EndpointMatcher Service
====================================

For complex filtering scenarios, use the ``EndpointMatcher`` service.

Basic Usage
-----------

Inject the service and use it in your listener:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
   use Queo\SimpleRestApi\Service\EndpointMatcher;
   use MyVendor\MyExtension\Controller\UserController;
   use MyVendor\MyExtension\Controller\ProductController;

   final readonly class CustomHeaderListener
   {
       public function __construct(
           private EndpointMatcher $matcher
       ) {
       }

       public function __invoke(ModifyApiResponseEvent $event): void
       {
           // Match specific methods on specific controllers
           if ($this->matcher->matches($event, [
               UserController::class => ['getUser', 'updateUser'],
               ProductController::class => 'getProduct'
           ])) {
               // Add custom headers
               $response = $event->getResponse();
               $response = $response->withHeader('X-Custom-Header', 'value');
               $event->setResponse($response);
           }
       }
   }

Wildcard Matching
-----------------

Match all methods on a controller:

.. code-block:: php

   if ($this->matcher->matches($event, [
       UserController::class => '*',  // All methods on UserController
       ProductController::class => ['getProduct', 'listProducts']
   ])) {
       // Execute for all UserController methods and specific ProductController methods
   }

Path Wildcards
--------------

Match path patterns with wildcards:

.. code-block:: php

   // Match all endpoints under /v1/users/
   if ($this->matcher->matchesPath($event, '/v1/users/*')) {
       // Matches: /v1/users/123, /v1/users/123/posts, etc.
   }

   // Match all admin endpoints
   if ($this->matcher->matchesPath($event, '/v1/admin/*')) {
       // Matches any path starting with /v1/admin/
   }

HTTP Method Filtering
---------------------

Filter by HTTP method:

.. code-block:: php

   // Only for POST requests
   if ($this->matcher->usesHttpMethod($event, 'POST')) {
       // Validate CSRF token
   }

   // Only for GET requests with caching
   if ($this->matcher->usesHttpMethod($event, 'GET')
       && $event->hasTag('cacheable')) {
       // Add cache headers
   }

Tag Checking with Matcher
--------------------------

The matcher also provides tag checking methods:

.. code-block:: php

   // Check single tag
   if ($this->matcher->hasTag($event, 'authenticated')) {
       // Validate authentication
   }

   // Check any of multiple tags
   if ($this->matcher->hasAnyTag($event, ['public', 'guest-allowed'])) {
       // Allow access
   }

   // Check all tags present
   if ($this->matcher->hasAllTags($event, ['authenticated', 'admin-only'])) {
       // Check admin permissions
   }

Complete Examples
=================

Example 1: Authentication Listener
-----------------------------------

Restrict authentication checks to tagged endpoints:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
   use TYPO3\CMS\Core\Http\Response;

   final class AuthenticationListener
   {
       public function __invoke(BeforeParameterMappingEvent $event): void
       {
           // Only check authentication for tagged endpoints
           if (!$event->hasTag('authenticated')) {
               return; // Public endpoint, skip authentication
           }

           $request = $event->getApiRequest();
           $token = $request->getRequest()->getHeaderLine('Authorization');

           if (!$this->isValidToken($token)) {
               throw new \RuntimeException('Invalid authentication token', 401);
           }
       }

       private function isValidToken(string $token): bool
       {
           // Your authentication logic
           return !empty($token);
       }
   }

Example 2: Caching Headers
--------------------------

Add cache headers based on endpoint tags:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;

   final class CachingListener
   {
       public function __invoke(ModifyApiResponseEvent $event): void
       {
           $response = $event->getResponse();

           if ($event->hasTag('cacheable')) {
               // Add cache headers for cacheable endpoints
               $maxAge = $event->hasTag('short-lived') ? 300 : 3600;
               $response = $response
                   ->withHeader('Cache-Control', "public, max-age={$maxAge}")
                   ->withHeader('Vary', 'Accept-Encoding');
           } elseif ($event->hasTag('no-cache')) {
               // Prevent caching
               $response = $response
                   ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                   ->withHeader('Pragma', 'no-cache')
                   ->withHeader('Expires', '0');
           }

           $event->setResponse($response);
       }
   }

Example 3: CORS Headers for Specific Controllers
-------------------------------------------------

Use the matcher service for complex filtering:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
   use Queo\SimpleRestApi\Service\EndpointMatcher;
   use MyVendor\MyExtension\Controller\PublicApiController;
   use MyVendor\MyExtension\Controller\WebhookController;

   final readonly class CorsListener
   {
       public function __construct(
           private EndpointMatcher $matcher
       ) {
       }

       public function __invoke(ModifyApiResponseEvent $event): void
       {
           // Only add CORS headers for public API and webhooks
           if (!$this->matcher->matches($event, [
               PublicApiController::class => '*',
               WebhookController::class => '*'
           ])) {
               return;
           }

           $response = $event->getResponse();
           $response = $response
               ->withHeader('Access-Control-Allow-Origin', '*')
               ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
               ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

           $event->setResponse($response);
       }
   }

Example 4: Loading Extbase Models
----------------------------------

Load related data for specific endpoints:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
   use MyVendor\MyExtension\Controller\UserController;
   use MyVendor\MyExtension\Domain\Repository\UserRepository;

   final readonly class LoadUserModelListener
   {
       public function __construct(
           private UserRepository $userRepository
       ) {
       }

       public function __invoke(AfterParameterMappingEvent $event): void
       {
           // Only for UserController::getUser
           if (!$event->isEndpoint(UserController::class, 'getUser')) {
               return;
           }

           $parameters = $event->getMethodParameters();
           $userId = $parameters[0] ?? null;

           if ($userId !== null) {
               // Load user model and replace ID with object
               $user = $this->userRepository->findByUid($userId);
               if ($user !== null) {
                   $event->overrideMethodParameters([$user]);
               }
           }
       }
   }

Best Practices
==============

1. **Start Simple, Add Complexity as Needed**

   * Use helper methods for single endpoint checks
   * Add tags when grouping multiple endpoints
   * Use EndpointMatcher for complex scenarios

2. **Use Type-Safe Class Constants**

   .. code-block:: php

      // Good: Type-safe, refactoring-friendly
      $event->isEndpoint(UserController::class, 'getUser')

      // Avoid: String-based, fragile
      if (str_contains($event->getApiEndpoint()->path, '/users/'))

3. **Tag Consistently**

   * Establish naming conventions for tags
   * Document tag meanings in your project
   * Use descriptive, domain-specific tag names

4. **Return Early**

   .. code-block:: php

      public function __invoke(BeforeParameterMappingEvent $event): void
      {
          // Return early if not applicable
          if (!$event->hasTag('authenticated')) {
              return;
          }

          // Process only relevant endpoints
          $this->checkAuthentication($event);
      }

5. **Combine Approaches**

   .. code-block:: php

      // Combine tags and class checking
      if ($event->hasTag('admin-only')
          && $event->isEndpoint(UserController::class, 'deleteUser')) {
          // Extra verification for critical operations
      }

Performance Considerations
==========================

The helper methods and EndpointMatcher service are optimized for performance:

* **Helper methods** - Direct property access, minimal overhead
* **Tag checking** - Simple array lookups using ``in_array()``
* **EndpointMatcher** - Lazy evaluation, stops at first match

For best performance:

* Use early returns to skip unnecessary processing
* Prefer direct helper methods over the matcher service for simple checks
* Cache expensive lookups in listener properties when possible

Summary
=======

==================  ========================  =========================
Approach            Best For                  Example
==================  ========================  =========================
Helper Methods      Single endpoint checks    ``$event->isEndpoint()``
Tags                Grouping endpoints        ``$event->hasTag('auth')``
EndpointMatcher     Complex filtering         Wildcards, multiple classes
==================  ========================  =========================

All three approaches work with all events:

* BeforeParameterMappingEvent
* AfterParameterMappingEvent
* ModifyApiResponseEvent

Choose the approach that best fits your use case, or combine them for maximum flexibility.
