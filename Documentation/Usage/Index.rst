.. include:: /Includes.rst.txt

.. _usage:

=====
Usage
=====

This chapter provides practical examples for creating REST API endpoints.

Basic Endpoint
==============

The simplest API endpoint returns a JSON response:

.. code-block:: php

   <?php
   namespace MyVendor\MyExtension\Controller;

   use Psr\Http\Message\ResponseInterface;
   use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
   use TYPO3\CMS\Core\Http\JsonResponse;

   final class ApiController
   {
       #[AsApiEndpoint(method: 'GET', path: '/v1/hello')]
       public function hello(): ResponseInterface
       {
           return new JsonResponse(['message' => 'Hello, World!']);
       }
   }

This endpoint will be accessible at: `/api/v1/hello`

With Documentation
------------------

Add summary and description for better documentation:

.. code-block:: php

   #[AsApiEndpoint(
       method: 'GET',
       path: '/v1/hello',
       summary: 'Simple hello world endpoint',
       description: 'Returns a friendly greeting message in JSON format'
   )]
   public function hello(): ResponseInterface
   {
       return new JsonResponse(['message' => 'Hello, World!']);
   }

The documentation will appear in the backend module.

URL Parameters
==============

Extract parameters from the URL path:

.. code-block:: php

   /**
    * @param int $userId The ID of the user to fetch
    */
   #[AsApiEndpoint(
       method: 'GET',
       path: '/v1/users/{userId}',
       summary: 'Get user by ID',
       description: 'Fetches a single user by their unique identifier'
   )]
   public function getUser(int $userId): ResponseInterface
   {
       // Your logic to fetch user
       $user = $this->userRepository->findByUid($userId);

       if (!$user) {
           return new JsonResponse(
               ['error' => 'User not found'],
               404
           );
       }

       return new JsonResponse([
           'id' => $user->getUid(),
           'name' => $user->getName(),
       ]);
   }

URL: `/api/v1/users/123` → `$userId = 123`

Multiple Parameters
-------------------

.. code-block:: php

   /**
    * @param int $userId The ID of the user
    * @param int $postId The ID of the post
    */
   #[AsApiEndpoint(
       method: 'GET',
       path: '/v1/users/{userId}/posts/{postId}',
       summary: 'Get user post',
       description: 'Fetches a specific post from a specific user'
   )]
   public function getUserPost(int $userId, int $postId): ResponseInterface
   {
       // Fetch post logic
       return new JsonResponse([
           'userId' => $userId,
           'postId' => $postId,
           'title' => 'Example Post'
       ]);
   }

URL: `/api/v1/users/123/posts/456` → `$userId = 123`, `$postId = 456`

Parameter Types
---------------

Supported parameter types:

* `int` - Integer values
* `string` - String values
* `float` - Floating point numbers
* `bool` - Boolean values (true/false, 1/0)

.. code-block:: php

   /**
    * @param string $slug The URL-friendly slug identifier
    */
   #[AsApiEndpoint(
       method: 'GET',
       path: '/v1/pages/{slug}',
       summary: 'Get page by slug'
   )]
   public function getPageBySlug(string $slug): ResponseInterface
   {
       return new JsonResponse(['slug' => $slug]);
   }

Accessing Request Data
======================

POST Request with Body
----------------------

Use `ServerRequestInterface` to access request body and headers:

.. code-block:: php

   use Psr\Http\Message\ServerRequestInterface;

   #[AsApiEndpoint(
       method: 'POST',
       path: '/v1/users',
       summary: 'Create new user',
       description: 'Creates a new user from JSON request body'
   )]
   public function createUser(ServerRequestInterface $request): ResponseInterface
   {
       $body = json_decode(
           $request->getBody()->getContents(),
           true
       );

       $name = $body['name'] ?? '';
       $email = $body['email'] ?? '';

       // Validation
       if (empty($name) || empty($email)) {
           return new JsonResponse(
               ['error' => 'Name and email are required'],
               400
           );
       }

       // Create user logic here
       $newUser = $this->userRepository->create($name, $email);

       return new JsonResponse(
           ['id' => $newUser->getUid(), 'name' => $name],
           201
       );
   }

Request Headers
---------------

.. code-block:: php

   public function myEndpoint(ServerRequestInterface $request): ResponseInterface
   {
       $authHeader = $request->getHeader('Authorization')[0] ?? '';
       $contentType = $request->getHeader('Content-Type')[0] ?? '';

       // Process headers...
   }

Query Parameters
----------------

Access GET query parameters:

.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/v1/search')]
   public function search(ServerRequestInterface $request): ResponseInterface
   {
       $queryParams = $request->getQueryParams();
       $searchTerm = $queryParams['q'] ?? '';
       $limit = (int)($queryParams['limit'] ?? 10);

       // Search logic...
       return new JsonResponse(['results' => []]);
   }

URL: `/api/v1/search?q=typo3&limit=20`

Combining URL Parameters with ServerRequest
--------------------------------------------

You can combine URL path parameters with `ServerRequestInterface` to access both
the URL parameters and the request body/headers/query parameters:

.. code-block:: php

   use Psr\Http\Message\ServerRequestInterface;

   /**
    * @param int $userId The ID of the user to update
    */
   #[AsApiEndpoint(
       method: 'PATCH',
       path: '/v1/users/{userId}',
       summary: 'Update user',
       description: 'Updates a user with data from request body'
   )]
   public function updateUser(
       int $userId,
       ServerRequestInterface $request
   ): ResponseInterface {
       // Get user ID from URL path
       $user = $this->userRepository->findByUid($userId);

       if (!$user) {
           return new JsonResponse(['error' => 'User not found'], 404);
       }

       // Get update data from request body
       $body = json_decode($request->getBody()->getContents(), true);

       // Update user properties
       if (isset($body['name'])) {
           $user->setName($body['name']);
       }
       if (isset($body['email'])) {
           $user->setEmail($body['email']);
       }

       $this->userRepository->update($user);

       return new JsonResponse([
           'id' => $userId,
           'name' => $user->getName(),
           'email' => $user->getEmail()
       ]);
   }

**Usage:**

.. code-block:: bash

   curl -X PATCH https://example.com/api/v1/users/123 \
     -H "Content-Type: application/json" \
     -d '{"name":"John Doe","email":"john@example.com"}'

This pattern is useful when you need:

* URL parameters for resource identification (e.g., user ID, post ID)
* Request body for data payload (e.g., update data, creation data)
* Request headers for authentication or content negotiation
* Query parameters for filtering or pagination

HTTP Methods
============

GET Requests
------------

.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/v1/items')]
   public function listItems(): ResponseInterface
   {
       // Fetch and return list
   }

POST Requests
-------------

.. code-block:: php

   #[AsApiEndpoint(method: 'POST', path: '/v1/items')]
   public function createItem(ServerRequestInterface $request): ResponseInterface
   {
       // Create new item
   }

PUT Requests
------------

.. code-block:: php

   #[AsApiEndpoint(method: 'PUT', path: '/v1/items/{id}')]
   public function updateItem(int $id, ServerRequestInterface $request): ResponseInterface
   {
       // Update existing item
   }

DELETE Requests
---------------

.. code-block:: php

   #[AsApiEndpoint(method: 'DELETE', path: '/v1/items/{id}')]
   public function deleteItem(int $id): ResponseInterface
   {
       // Delete item
       return new JsonResponse(['deleted' => true]);
   }

Response Types
==============

JSON Response
-------------

Most common response type:

.. code-block:: php

   return new JsonResponse([
       'success' => true,
       'data' => $myData
   ]);

With Status Code
----------------

.. code-block:: php

   // 201 Created
   return new JsonResponse(['id' => $newId], 201);

   // 404 Not Found
   return new JsonResponse(['error' => 'Not found'], 404);

   // 400 Bad Request
   return new JsonResponse(['error' => 'Invalid input'], 400);

Custom Headers
--------------

.. code-block:: php

   $response = new JsonResponse(['data' => $data]);
   return $response->withHeader('X-Custom-Header', 'value');

Error Handling
==============

Try-Catch Pattern
-----------------

.. code-block:: php

   public function myEndpoint(int $id): ResponseInterface
   {
       try {
           $item = $this->repository->findByUid($id);

           if (!$item) {
               return new JsonResponse(
                   ['error' => 'Item not found'],
                   404
               );
           }

           return new JsonResponse(['item' => $item->toArray()]);

       } catch (\Exception $e) {
           return new JsonResponse(
               ['error' => 'Internal server error'],
               500
           );
       }
   }

Validation
----------

.. code-block:: php

   public function createUser(ServerRequestInterface $request): ResponseInterface
   {
       $body = json_decode($request->getBody()->getContents(), true);

       // Validation
       $errors = [];

       if (empty($body['email'])) {
           $errors[] = 'Email is required';
       } elseif (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
           $errors[] = 'Invalid email format';
       }

       if (empty($body['name'])) {
           $errors[] = 'Name is required';
       }

       if (!empty($errors)) {
           return new JsonResponse(['errors' => $errors], 400);
       }

       // Process valid data...
   }

Dependency Injection
====================

Use constructor injection for repositories and services:

.. code-block:: php

   use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

   final class ApiController
   {
       public function __construct(
           private readonly UserRepository $userRepository,
           private readonly LoggerInterface $logger
       ) {}

       #[AsApiEndpoint(method: 'GET', path: '/v1/users')]
       public function listUsers(): ResponseInterface
       {
           $users = $this->userRepository->findAll();
           $this->logger->info('Listed all users');

           return new JsonResponse([
               'users' => array_map(
                   fn($user) => ['id' => $user->getUid(), 'name' => $user->getName()],
                   $users->toArray()
               )
           ]);
       }
   }

Testing Your Endpoints
=======================

Using cURL
----------

.. code-block:: bash

   # GET request
   curl https://your-domain.com/api/v1/hello

   # GET with parameters
   curl https://your-domain.com/api/v1/users/123

   # POST with JSON
   curl -X POST https://your-domain.com/api/v1/users \
     -H "Content-Type: application/json" \
     -d '{"name":"John Doe","email":"john@example.com"}'

Using Browser
-------------

For GET requests, simply navigate to:

.. code-block:: text

   https://your-domain.com/api/v1/hello

Backend Module
--------------

Check the **Site** → **REST API Endpoints** module to verify your endpoint
is registered and view its documentation.

Modifying Responses
===================

The extension provides a PSR-14 event (`ModifyApiResponseEvent`) that allows you to
modify API responses before they are returned to the client. This is useful for:

* Adding CORS headers
* Adding custom headers (API version, request tracking, etc.)
* Adding caching headers
* Logging responses
* Modifying response content

Creating an Event Listener
---------------------------

Create an event listener in your extension:

.. code-block:: php

   <?php

   declare(strict_types=1);

   namespace MyVendor\MyExtension\EventListener;

   use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;

   final readonly class ApiResponseListener
   {
       public function addCustomHeaders(ModifyApiResponseEvent $event): void
       {
           $response = $event->getResponse();

           // Add CORS headers
           $response = $response
               ->withHeader('Access-Control-Allow-Origin', '*')
               ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
               ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

           // Add API version
           $response = $response->withHeader('X-API-Version', '1.0');

           // Add request tracking ID
           $response = $response->withHeader('X-Request-ID', uniqid('req_', true));

           $event->setResponse($response);
       }
   }

Register in `Configuration/Services.yaml`:

.. code-block:: yaml

   services:
     MyVendor\MyExtension\EventListener\ApiResponseListener:
       tags:
         - name: event.listener
           identifier: 'my-extension/api-response-headers'
           event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
           method: 'addCustomHeaders'

Conditional Response Modification
----------------------------------

You can access the endpoint information to conditionally modify responses:

.. code-block:: php

   public function addCachingHeaders(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();
       $response = $event->getResponse();

       // Only cache GET requests
       if ($endpoint->httpMethod === 'GET') {
           $response = $response->withHeader('Cache-Control', 'public, max-age=300');
       } else {
           $response = $response->withHeader('Cache-Control', 'no-cache, no-store');
       }

       $event->setResponse($response);
   }

Accessing Request Information
------------------------------

The event also provides access to the API request:

.. code-block:: php

   public function logApiCall(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();
       $apiRequest = $event->getApiRequest();
       $response = $event->getResponse();

       // Log the API call
       $this->logger->info(sprintf(
           'API Call: %s %s -> HTTP %d',
           $endpoint->httpMethod,
           $endpoint->path,
           $response->getStatusCode()
       ));
   }

Example: Complete Response Replacement
---------------------------------------

You can even replace the entire response if needed:

.. code-block:: php

   public function modifyResponse(ModifyApiResponseEvent $event): void
   {
       $endpoint = $event->getEndpoint();

       // Special handling for specific endpoint
       if ($endpoint->path === '/v1/special') {
           $newResponse = new JsonResponse(['custom' => 'data'], 200);
           $event->setResponse($newResponse);
       }
   }

For more details and examples, see :ref:`developer` documentation.

Best Practices
==============

1. **Always document parameters** - Use PHPDoc `@param` tags for automatic documentation
2. **Use type hints** - Leverage PHP's type system for parameter validation
3. **Return appropriate status codes** - 200 for success, 201 for created, 404 for not found, etc.
4. **Handle errors gracefully** - Always catch exceptions and return meaningful error messages
5. **Validate input** - Never trust user input, always validate
6. **Use dependency injection** - Don't instantiate dependencies manually
7. **Keep endpoints focused** - One endpoint should do one thing well
8. **Version your API** - Use `/v1/`, `/v2/` prefixes for versioning
9. **Use events for cross-cutting concerns** - CORS, logging, caching should be in event listeners

Next Steps
==========

For more advanced topics, see :ref:`developer` documentation.
