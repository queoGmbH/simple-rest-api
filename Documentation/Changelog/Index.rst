.. include:: /Includes.rst.txt

.. _changelog:

=========
Changelog
=========

All notable changes to this project will be documented in this file.

Version 0.2.0-rc1
=================

Released: 2025-10-31

Added
-----

* Backend module to list all registered REST API endpoints
* Endpoint documentation with summary and description fields
* Parameter documentation with automatic type and description extraction
* Reflection-based parameter information extraction
* PHPDoc parsing for parameter descriptions
* Bootstrap 5 accordion UI in backend module
* Visual endpoint overview with collapsible details
* `ApiEndpointParameter` value object for parameter details
* Comprehensive parameter table showing name, type, and description

Changed
-------

* Enhanced `ApiEndpoint` to include summary, description, and parameters array
* Updated `AsApiEndpoint` attribute to accept summary and description
* Improved `ApiEndpointProvider` with reflection-based parameter extraction
* Backend module template redesigned with TYPO3-native panel layout

Fixed
-----

* Backend module icon registration for TYPO3 13 compatibility
* Icon now properly registers via `ext_localconf.php` instead of `Configuration/Backend/Icons.php`
* PHPStan level 9 compliance with proper type annotations
* Code style issues identified by PHP_CodeSniffer
* Rector modernization for PHP 8+ syntax

Technical
---------

* Added composer-require-checker configuration
* Improved test coverage for parameter extraction
* Added proper `@param class-string` annotations for reflection
* Removed unused imports and improved code quality
* Updated tests to use `stdClass::class` instead of `\stdClass::class`
* Modernized exception catching to PHP 8 syntax

Version 0.1.0
=============

Released: 2025-01-15

Initial release with core functionality.

Added
-----

* PHP attribute `#[AsApiEndpoint]` to mark methods as API endpoints
* Automatic endpoint registration via dependency injection
* URL parameter mapping to method arguments
* Support for scalar parameter types (int, string, float, bool)
* PSR-7 ServerRequest integration
* Custom TYPO3 route enhancer for `/api/` paths
* Middleware stack for request processing:

  * `ApiResolverMiddleware` - Main endpoint resolution
  * `ApiAspectMiddleware` - Context handling
  * `CacheHashFixer` - Cache hash management

* `ApiEndpointProvider` for endpoint management
* PSR-14 events:

  * `BeforeParameterMappingEvent`
  * `AfterParameterMappingEvent`

* Value objects:

  * `ApiEndpoint` - Endpoint representation
  * `ApiRequest` - API request representation
  * `Parameters` collection

* Comprehensive test suite:

  * Unit tests for core components
  * Integration tests for routing

* Code quality tools:

  * PHPStan level 9
  * PHP_CodeSniffer
  * Rector
  * GrumPHP pre-commit hooks
  * composer-normalize

Documentation
-------------

* README with basic usage examples
* PHPDoc for all public APIs
* Example controller with test endpoints

Configuration
-------------

* Route enhancer configuration file
* Service container configuration
* Cache configuration for endpoint registry
* Backend module configuration (planned for 0.2.0)

.. _changelog-upgrade:

Upgrade Guide
=============

Upgrading from 0.1.x to 0.2.x
-----------------------------

The 0.2.0 release is backward compatible with 0.1.x. No breaking changes.

Optional Enhancements
~~~~~~~~~~~~~~~~~~~~~

To take advantage of new documentation features:

1. **Add summary and description to endpoints**

   .. code-block:: php

      // Before (still works)
      #[AsApiEndpoint(method: 'GET', path: '/v1/users')]

      // After (recommended)
      #[AsApiEndpoint(
          method: 'GET',
          path: '/v1/users',
          summary: 'List all users',
          description: 'Returns a paginated list of all users in the system'
      )]

2. **Add PHPDoc for parameters**

   .. code-block:: php

      /**
       * @param int $userId The unique identifier of the user
       */
      #[AsApiEndpoint(method: 'GET', path: '/v1/users/{userId}')]
      public function getUser(int $userId): ResponseInterface

3. **Clear caches after upgrade**

   .. code-block:: bash

      vendor/bin/typo3 cache:flush

4. **Check the new backend module**

   Navigate to **Site** → **REST API Endpoints** to see your documented endpoints.

Migration Notes
===============

From Other REST Extensions
---------------------------

If migrating from other TYPO3 REST API extensions:

**From Extbase REST Controllers:**

.. code-block:: php

   // Before: Extbase
   class UserController extends ActionController
   {
       public function listAction(): ResponseInterface
       {
           // ...
       }
   }

   // After: Simple REST API
   #[AsApiEndpoint(method: 'GET', path: '/v1/users')]
   public function listUsers(): ResponseInterface
   {
       return new JsonResponse(['users' => $users]);
   }

**From Custom Routing:**

Remove custom routing configuration and use the route enhancer instead:

.. code-block:: yaml

   # config/sites/<site>/config.yaml
   imports:
     - { resource: "EXT:simple_rest_api/Configuration/Yaml/RouteEnhancer.yaml" }

Deprecation Notices
===================

Currently, there are no deprecated features.

Future deprecations will be announced in this changelog at least one major
version before removal.

Roadmap
=======

Planned for Future Versions
----------------------------

Version 0.3.0
~~~~~~~~~~~~~

* OpenAPI/Swagger documentation generation
* Export endpoint documentation as JSON
* Configurable API base path (not hardcoded to `/api/`)
* Support for PUT and PATCH methods (currently experimental)

Version 0.4.0
~~~~~~~~~~~~~

* Built-in request validation framework
* Response serialization helpers
* More PSR-14 events for customization
* CLI command to list and test endpoints

Version 1.0.0
~~~~~~~~~~~~~

* Stable API - no breaking changes in 1.x series
* Complete OpenAPI support
* Performance optimizations
* Comprehensive documentation
* Production-ready examples

Contributing
============

See version history at:

* GitLab: https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api

To contribute, see :ref:`developer` documentation.
