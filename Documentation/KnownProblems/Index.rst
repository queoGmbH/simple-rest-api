.. include:: /Includes.rst.txt

.. _known-problems:

==============
Known Problems
==============

This chapter lists known issues and their solutions.

Endpoints Not Accessible
=========================

Problem
-------

API endpoints return 404 errors even though they appear in the backend module.

Solutions
---------

1. **Clear System Cache**

   The most common cause. After adding or modifying endpoints:

   .. code-block:: bash

      vendor/bin/typo3 cache:flush

   Or in backend: **Admin Tools** → **Flush TYPO3 and PHP Cache**

2. **Verify Route Enhancer Configuration**

   Check `config/sites/<your-site>/config.yaml`:

   .. code-block:: yaml

      routeEnhancers:
        SimpleRestApiEnhancer:
          type: SimpleRestApiEnhancer

   Or use the import:

   .. code-block:: yaml

      imports:
        - { resource: "EXT:simple_rest_api/Configuration/Yaml/RouteEnhancer.yaml" }

3. **Check Web Server Configuration**

   Ensure your web server (Apache/nginx) allows requests to `/api/*` paths.

   For Apache, verify `.htaccess` includes:

   .. code-block:: apache

      RewriteRule ^api/ - [L]

Backend Module Icon Not Showing
================================

Problem
-------

The backend module shows a broken icon instead of the REST API icon.

Solution
--------

This is fixed in version 0.2.0-rc1 and later. The icon registration was moved
to `ext_localconf.php` for TYPO3 13 compatibility.

If you're using an older version, upgrade:

.. code-block:: bash

   composer update queo/simple-rest-api

Parameter Type Mismatch
========================

Problem
-------

Endpoint receives wrong parameter type (e.g., string instead of int).

.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/v1/users/{userId}')]
   public function getUser(int $userId): ResponseInterface
   {
       // $userId might be string
   }

Solution
--------

The extension automatically type-casts parameters based on method signature.
Ensure your type hints are correct:

.. code-block:: php

   public function getUser(int $userId): ResponseInterface  // Correct
   public function getUser($userId): ResponseInterface       // No type casting

If issues persist, check the parameter value in the URL.

Endpoints Not Appearing in Backend Module
==========================================

Problem
-------

Created endpoints don't show up in **Site** → **REST API Endpoints**.

Solutions
---------

1. **Verify Service Autoconfiguration**

   Ensure your extension's `Configuration/Services.yaml` contains the resource configuration:

   .. code-block:: yaml

      services:
        _defaults:
          autowire: true
          autoconfigure: true

        Vendor\MyExtension\:
          resource: '../Classes/*'

   Without the `resource` directive, TYPO3 won't scan your classes and detect
   the `#[AsApiEndpoint]` attributes.

2. **Clear Cache**

   Endpoint registration happens during cache warmup:

   .. code-block:: bash

      vendor/bin/typo3 cache:flush

3. **Check Namespace**

   Ensure your controller is in a properly configured namespace:

   .. code-block:: php

      namespace Vendor\Extension\Controller;  // Correct
      namespace Vendor\Extension\Api;         // Also valid if configured

JSON Response Not Formatted
============================

Problem
-------

JSON responses are minified and hard to read during development.

Solution
--------

Use a browser extension or set JSON encoding options:

.. code-block:: php

   $data = ['key' => 'value'];
   $json = json_encode($data, JSON_PRETTY_PRINT);

   return new Response(
       $json,
       200,
       ['Content-Type' => 'application/json']
   );

Or use JsonResponse with pretty print in development:

.. code-block:: php

   if (Environment::getContext()->isDevelopment()) {
       return new JsonResponse(
           $data,
           200,
           [],
           JSON_PRETTY_PRINT
       );
   }

CORS Issues
===========

Problem
-------

Browser blocks API requests due to CORS policy.

.. code-block:: text

   Access to fetch at 'https://example.com/api/v1/data' from origin
   'https://app.example.com' has been blocked by CORS policy

Solution
--------

Add CORS headers in your endpoint or via middleware:

.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/v1/data')]
   public function getData(): ResponseInterface
   {
       $response = new JsonResponse(['data' => 'value']);

       return $response
           ->withHeader('Access-Control-Allow-Origin', '*')
           ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
           ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
   }

For production, create a CORS middleware instead of adding headers to each endpoint.

POST Request Returns 404
=========================

Problem
-------

POST requests to valid endpoint return 404, but GET works.

Solution
--------

Verify the HTTP method in the attribute matches your request:

.. code-block:: php

   #[AsApiEndpoint(method: 'POST', path: '/v1/users')]  // Note: POST
   public function createUser(ServerRequestInterface $request): ResponseInterface

Test with cURL:

.. code-block:: bash

   curl -X POST https://example.com/api/v1/users \
     -H "Content-Type: application/json" \
     -d '{"name":"Test"}'

Cache Hash Errors
=================

Problem
-------

TYPO3 shows cache hash errors in logs for API requests.

Solution
--------

The extension includes `CacheHashFixer` middleware to handle this. If you still
see errors, verify the middleware is loaded:

Check `Configuration/RequestMiddlewares.php` includes:

.. code-block:: php

   'simple-rest-api/cache-hash-fixer' => [
       'target' => CacheHashFixer::class,
       // ...
   ]

Class Not Found Errors
======================

Problem
-------

.. code-block:: text

   Class "Queo\SimpleRestApi\Attribute\AsApiEndpoint" not found

Solution
--------

1. **Run composer install/update**

   .. code-block:: bash

      composer install

2. **Clear autoloader cache**

   .. code-block:: bash

      composer dump-autoload

3. **Verify extension is activated**

   .. code-block:: bash

      vendor/bin/typo3 extension:activate simple_rest_api

PHPDoc Not Parsed
=================

Problem
-------

Parameter descriptions don't appear in backend module despite PHPDoc.

Solution
--------

Ensure PHPDoc format is correct:

.. code-block:: php

   /**
    * @param int $userId The user identifier  // Correct
    */

   /** @param int $userId The user identifier */  // Also works

   // @param int $userId The user identifier     // WRONG - not a PHPDoc

The pattern must match: `@param TYPE $NAME DESCRIPTION`

Performance Issues
==================

Problem
-------

API endpoints are slow to respond.

Solutions
---------

1. **Enable OpCache**

   Ensure PHP OpCache is enabled in production.

2. **Use TYPO3 Caching**

   Cache expensive operations:

   .. code-block:: php

      $cacheKey = 'my_api_data_' . $id;
      $cache = GeneralUtility::makeInstance(CacheManager::class)
          ->getCache('my_cache');

      if (!$cache->has($cacheKey)) {
          $data = $this->expensiveOperation($id);
          $cache->set($cacheKey, $data, [], 3600);
      }

      return new JsonResponse($cache->get($cacheKey));

3. **Optimize Database Queries**

   Use indexes and avoid N+1 queries.

4. **Disable Debug Mode in Production**

   .. code-block:: yaml

      # .ddev/config.yaml - only for development
      web_environment:
        - TYPO3_CONTEXT=Production

Memory Limit Exceeded
======================

Problem
-------

.. code-block:: text

   Fatal error: Allowed memory size exhausted

Solution
--------

Increase PHP memory limit in `php.ini` or `.htaccess`:

.. code-block:: ini

   memory_limit = 256M

For large datasets, use pagination:

.. code-block:: php

   #[AsApiEndpoint(method: 'GET', path: '/v1/items')]
   public function listItems(ServerRequestInterface $request): ResponseInterface
   {
       $page = (int)($request->getQueryParams()['page'] ?? 1);
       $limit = 50;
       $offset = ($page - 1) * $limit;

       $items = $this->repository->findByLimit($limit, $offset);

       return new JsonResponse([
           'items' => $items,
           'page' => $page,
           'total' => $this->repository->count()
       ]);
   }

Still Having Problems?
======================

If your issue isn't listed here:

1. **Check TYPO3 Documentation** - https://docs.typo3.org
2. **Review Extension Code** - The codebase is well-documented
3. **Create an Issue** - Report bugs on GitLab
4. **Ask the Community** - TYPO3 Slack or Stack Overflow

When reporting issues, include:

* TYPO3 version
* PHP version
* Extension version
* Error messages
* Steps to reproduce
* Expected vs actual behavior
