.. include:: /Includes.rst.txt

.. _filtering-event-listeners:

==========================================
Filtering Event Listeners by Endpoint
==========================================

When working with PSR-14 events in the Simple REST API extension, you often want
to restrict event listeners to specific endpoints rather than executing for all API requests.

This guide shows how to filter event listeners based on endpoint properties.

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

Solution: Use ApiEndpoint Methods
==================================

All events provide access to the ``ApiEndpoint`` object, which contains methods
to check endpoint properties. This is the **only** way to filter endpoints.

.. code-block:: php

   public function __invoke(BeforeParameterMappingEvent $event): void
   {
       $endpoint = $event->getApiEndpoint();

       if ($endpoint->hasTag('authenticated')) {
           // Process authenticated endpoints
       }
   }

Available Methods
=================

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
           $endpoint = $event->getApiEndpoint();

           // Only execute for specific endpoint
           if ($endpoint->isEndpoint(UserController::class, 'getUser')) {
               // Modify parameters only for UserController::getUser
               $params = $event->getPathParameters();
               // ... modify parameters
           }
       }
   }

Check Multiple Endpoints
-------------------------

Use ``isAnyEndpoint()`` to check if the endpoint matches any of several class/method combinations:

.. code-block:: php

   use MyVendor\MyExtension\Controller\UserController;
   use MyVendor\MyExtension\Controller\ProductController;

   public function __invoke(BeforeParameterMappingEvent $event): void
   {
       $endpoint = $event->getApiEndpoint();

       // Check multiple specific methods
       if ($endpoint->isAnyEndpoint([
           UserController::class => ['getUser', 'updateUser'],
           ProductController::class => 'getProduct',
       ])) {
           // Execute for these specific endpoints
       }

       // Use wildcard to match all methods on a controller
       if ($endpoint->isAnyEndpoint([
           UserController::class => '*',
           ProductController::class => ['getProduct', 'listProducts'],
       ])) {
           // Execute for all UserController methods and specific ProductController methods
       }
   }

Check by Path Pattern
---------------------

Match endpoints by their URL path:

.. code-block:: php

   public function __invoke(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();

       // Match exact path (without version prefix)
       if ($endpoint->matchesPath('/users/{userId}')) {
           // Add custom headers for this specific endpoint
       }
   }

Check by Tags
-------------

Tags allow you to group endpoints by functionality:

.. code-block:: php

   public function __invoke(BeforeParameterMappingEvent $event): void
   {
       $endpoint = $event->getApiEndpoint();

       // Check single tag
       if ($endpoint->hasTag('authenticated')) {
           // Validate authentication token
       }

       // Check if endpoint has ANY of these tags
       if ($endpoint->hasAnyTag(['public', 'guest-allowed'])) {
           // Allow anonymous access
       }

       // Check if endpoint has ALL of these tags
       if ($endpoint->hasAllTags(['authenticated', 'admin-only'])) {
           // Check admin permissions
       }
   }

Method Reference
----------------

All ``ApiEndpoint`` objects provide these methods:

.. code-block:: php

   // Check class and method
   $endpoint->isEndpoint(UserController::class, 'getUser'): bool

   // Check multiple class/method combinations
   $endpoint->isAnyEndpoint([
       UserController::class => ['getUser', 'updateUser'],
       ProductController::class => '*',
   ]): bool

   // Check path (exact match only, without version prefix)
   $endpoint->matchesPath('/users/{userId}'): bool

   // Check tags
   $endpoint->hasTag('authenticated'): bool
   $endpoint->hasAnyTag(['public', 'cached']): bool
   $endpoint->hasAllTags(['authenticated', 'admin']): bool

   // Access properties
   $endpoint->className: string
   $endpoint->method: string
   $endpoint->path: string
   $endpoint->httpMethod: string
   $endpoint->tags: array

Endpoint Tags
=============

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
           path: '/users/{userId}',
           version: '1',
           tags: ['authenticated', 'user-management', 'cacheable']
       )]
       public function getUser(int $userId): ResponseInterface
       {
           return new JsonResponse(['user' => $userId]);
       }

       #[AsApiEndpoint(
           method: 'POST',
           path: '/users',
           version: '1',
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
           $endpoint = $event->getApiEndpoint();

           // Execute for all endpoints tagged as 'authenticated'
           if ($endpoint->hasTag('authenticated')) {
               // Validate authentication token
               $this->validateAuthentication($event->getApiRequest());
           }
       }
   }

Multiple Tag Conditions
-----------------------

Check for any or all tags:

.. code-block:: php

   $endpoint = $event->getApiEndpoint();

   // Execute if endpoint has ANY of these tags
   if ($endpoint->hasAnyTag(['public', 'guest-allowed'])) {
       // Allow anonymous access
   }

   // Execute ONLY if endpoint has ALL of these tags
   if ($endpoint->hasAllTags(['authenticated', 'admin-only'])) {
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
           $endpoint = $event->getApiEndpoint();

           // Only check authentication for tagged endpoints
           if (!$endpoint->hasTag('authenticated')) {
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
           $endpoint = $event->getEndpoint();
           $response = $event->getResponse();

           if ($endpoint->hasTag('cacheable')) {
               // Add cache headers for cacheable endpoints
               $maxAge = $endpoint->hasTag('short-lived') ? 300 : 3600;
               $response = $response
                   ->withHeader('Cache-Control', "public, max-age={$maxAge}")
                   ->withHeader('Vary', 'Accept-Encoding');
           } elseif ($endpoint->hasTag('no-cache')) {
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

Use ``isAnyEndpoint()`` to filter by multiple controllers:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
   use MyVendor\MyExtension\Controller\PublicApiController;
   use MyVendor\MyExtension\Controller\WebhookController;

   final class CorsListener
   {
       public function __invoke(ModifyApiResponseEvent $event): void
       {
           $endpoint = $event->getEndpoint();

           // Only add CORS headers for public API and webhooks
           if (!$endpoint->isAnyEndpoint([
               PublicApiController::class => '*',
               WebhookController::class => '*',
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
           $endpoint = $event->getEndpoint();

           // Only for UserController::getUser
           if (!$endpoint->isEndpoint(UserController::class, 'getUser')) {
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

Example 5: HTTP Method Filtering
---------------------------------

Filter by HTTP method:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;

   final class CsrfTokenListener
   {
       public function __invoke(BeforeParameterMappingEvent $event): void
       {
           $endpoint = $event->getApiEndpoint();

           // Only validate CSRF tokens for POST requests
           if (strtoupper($endpoint->httpMethod) !== 'POST') {
               return;
           }

           // Validate CSRF token
           $this->validateCsrfToken($event->getApiRequest());
       }

       private function validateCsrfToken($request): void
       {
           // Your CSRF validation logic
       }
   }

Best Practices
==============

1. **Always Get the Endpoint Object First**

   .. code-block:: php

      public function __invoke(BeforeParameterMappingEvent $event): void
      {
          $endpoint = $event->getApiEndpoint();

          if ($endpoint->hasTag('authenticated')) {
              // Process
          }
      }

2. **Use Type-Safe Class Constants**

   .. code-block:: php

      // Good: Type-safe, refactoring-friendly
      $endpoint->isEndpoint(UserController::class, 'getUser')

      // Avoid: String-based, fragile
      if (str_contains($endpoint->path, '/users/'))

3. **Tag Consistently**

   * Establish naming conventions for tags
   * Document tag meanings in your project
   * Use descriptive, domain-specific tag names

4. **Return Early**

   .. code-block:: php

      public function __invoke(BeforeParameterMappingEvent $event): void
      {
          $endpoint = $event->getApiEndpoint();

          // Return early if not applicable
          if (!$endpoint->hasTag('authenticated')) {
              return;
          }

          // Process only relevant endpoints
          $this->checkAuthentication($event);
      }

5. **Combine Multiple Checks**

   .. code-block:: php

      $endpoint = $event->getApiEndpoint();

      // Combine tags and class checking
      if ($endpoint->hasTag('admin-only')
          && $endpoint->isEndpoint(UserController::class, 'deleteUser')) {
          // Extra verification for critical operations
      }

Event-Specific Endpoint Methods
================================

Different events use different method names to get the endpoint:

.. code-block:: php

   // BeforeParameterMappingEvent
   $endpoint = $event->getApiEndpoint();

   // AfterParameterMappingEvent
   $endpoint = $event->getEndpoint();

   // ModifyApiResponseEvent
   $endpoint = $event->getEndpoint();

All returned ``ApiEndpoint`` objects have the same methods available.

Performance Considerations
==========================

The ``ApiEndpoint`` methods are optimized for performance:

* **isEndpoint()** - Direct property comparison
* **hasTag()** - Simple array lookup using ``in_array()``
* **hasAnyTag() / hasAllTags()** - Early return on match/mismatch

For best performance:

* Use early returns to skip unnecessary processing
* Cache the endpoint object in a variable if checking multiple conditions
* Prefer specific checks (``isEndpoint()``) over generic ones when possible

Summary
=======

**The Simple Approach:**

1. Get the endpoint object from the event
2. Call methods on the endpoint to check conditions
3. Process or return early based on the result

.. code-block:: php

   public function __invoke(BeforeParameterMappingEvent $event): void
   {
       $endpoint = $event->getApiEndpoint();

       if (!$endpoint->hasTag('my-tag')) {
           return;
       }

       // Your logic here
   }

**Available on all endpoints:**

* ``isEndpoint(class, method)`` - Check specific controller/method
* ``isAnyEndpoint([class => methods])`` - Check multiple controller/method combinations
* ``hasTag(tag)`` - Check single tag
* ``hasAnyTag([tags])`` - Check any of multiple tags
* ``hasAllTags([tags])`` - Check all tags present
* ``matchesPath(path)`` - Check exact path match

This approach works with all events:

* ``BeforeParameterMappingEvent``
* ``AfterParameterMappingEvent``
* ``ModifyApiResponseEvent``
