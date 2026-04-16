.. include:: /Includes.rst.txt

.. _changelog:

=========
Changelog
=========

All notable changes to this project will be documented in this file.

Version 0.3.1
=============

Released: 16.04.2026

Fixed
-----

* **Cache key typo corrected** — ``somple_rest_api`` → ``simple_rest_api`` in ``ext_localconf.php``

  * The cache configuration was silently registered under a misspelled key and never used

Added
-----

* **ext_emconf.php** for TYPO3 Extension Repository (TER) compatibility
* **LICENSE** file (GPL-2.0-or-later)
* **Resources/Public/Icons/Extension.svg** for TER extension listing
* **SECURITY.md** with vulnerability reporting policy and supported versions
* **CHANGELOG.md** in repository root for Packagist and GitHub visitors
* **GitHub mirror CI pipeline** — strips ``.claude/`` and ``CLAUDE.md`` before mirroring
  to ``github.com:queoGmbH/simple-rest-api`` on every push to ``main`` and every tag

Technical
---------

* ``composer.json`` extended with ``authors``, ``keywords``, ``homepage``, ``support`` fields
* ``Documentation/Settings.cfg`` version corrected to ``0.3.1``
* ``ext_emconf.php`` added to GrumPHP PHPStan and Rector ignore patterns (TYPO3 legacy format)

Version 0.3.0
=============

Released: 16.04.2026

Security
--------

* **[M-01] Security logging for unmatched API paths**

  * ``ApiResolverMiddleware`` now emits a ``WARNING`` log entry when the API base path
    is matched but no registered endpoint is found for the requested method/path
  * Log channel: ``simple_rest_api``

* **[M-02] Security logging on parameter coercion failure (400)**

  * ``ApiResolverMiddleware`` catches ``InvalidParameterException`` and logs a ``WARNING``
    with the parameter name before returning a ``400 Bad Request`` JSON response

* **[M-03] Validated type coercion for URL parameters**

  * Bare PHP casts (``(int)``, ``(float)``, ``(bool)``) replaced with ``filter_var()``-based
    validation in ``EndpointParameterResolver``
  * Invalid values (e.g. ``"abc"`` for an ``int`` parameter) now throw
    ``InvalidParameterException`` and return 400 instead of silently coercing to ``0``
  * Empty strings are explicitly rejected for numeric and boolean types
  * New exception class: ``Queo\SimpleRestApi\Exception\InvalidParameterException``

* **[L-01] Security notice added to** ``AsApiEndpoint`` **attribute**

  * PHPDoc block now includes an explicit ``SECURITY NOTICE`` documenting that
    authentication, authorization, rate limiting, and input validation beyond scalar
    type coercion are the consumer's responsibility

* **[L-02] CacheHashFixer restores global config after request handling**

  * Original values of ``$GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError']``
    and ``$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation']`` are now
    saved before mutation and restored in a ``finally`` block

* **[L-03] Remove** ``error_log()`` **from example event listener**

  * Removed ``logResponse()`` method from ``ApiResponseModifierExample`` which used
    ``error_log()`` instead of TYPO3's logging API

Added
-----

* ``LoggerInterface`` constructor injection in ``ApiResolverMiddleware`` (TYPO3 13
  ``LoggerInterfacePass`` autowires this automatically)
* ``InvalidParameterException`` as a dedicated exception class for parameter coercion
  failures
* ``psr/log: ^3.0`` as explicit Composer dependency

Technical
---------

* Extended test suite with ``#[DataProvider]``-driven coercion tests (16 valid, 8 invalid
  cases)
* ``HttpMethodsIntegrationTest`` and ``ApiResolverMiddlewareTest`` updated to inject
  ``NullLogger`` and set ``$resetSingletonInstances = true`` for TYPO3 test isolation

Version 0.2.4
=============

Released: 23.03.2026

Fixed
-----

* **CacheHashFixer now uses dynamic API base path** from extension configuration instead of hardcoded ``/api/``

  * Cache hash validation bypass now correctly applies when a custom base path (e.g. ``/rest/``, ``/services/``) is configured
  * Previously, API requests with a custom base path would not trigger the cHash fix and could result in TYPO3 404 errors

* **CacheHashFixer now respects language base path** in multi-language TYPO3 sites

  * For sites with language-specific base paths (e.g. ``/en/``, ``/de/``), the correct language base is now used
  * Previously, only the site base was used, causing the cHash fix to not trigger for non-default languages

Technical
---------

* Updated ``CacheHashFixer`` to resolve ``ExtensionConfiguration`` dynamically from the request context
* ``CacheHashFixer`` now reads ``SiteLanguage`` from the request attribute and prefers it over the site base path

Version 0.2.3
=============

Released: 16.01.2026

Documentation
-------------

* **Complete changelog documentation** for versions 0.2.1 and 0.2.2

  * Added comprehensive changelog entries for version 0.2.1 (multi-language support, integration tests)
  * Added comprehensive changelog entries for version 0.2.2 (debug mode configuration)
  * Documented all features, fixes, and technical changes
  * Added configuration examples and usage instructions

Technical
---------

* This is a documentation-only release with no code changes
* All functionality from 0.2.2 remains unchanged

Version 0.2.2
=============

Released: 09.12.2025

Added
-----

* **Debug mode configuration** to control visibility of extension's internal endpoints

  * New ``debugMode`` configuration option (default: ``false``)
  * When disabled, hides all endpoints from extension namespace (``Queo\SimpleRestApi\*``)
  * Prevents extension's example/test endpoints from appearing in backend module
  * Configurable via site settings or extension configuration
  * Automatic namespace-based filtering

* Added ``typo3/cms-lowlevel`` as dev dependency for Configuration module access

Changed
-------

* Renamed ``showInternalEndpoints()`` to ``isDebugMode()`` in ``ExtensionConfiguration``
* Changed configuration key from ``showInternalEndpoints`` to ``debugMode``
* Backend module now filters endpoints based on class namespace instead of tags
* Removed ``internal`` tags from TestController endpoints (no longer needed)

Fixed
-----

* **Missing tags parameter** in Services.php configuration preventing tag-based filtering
* Tag-based endpoint filtering now works correctly with proper service configuration

Configuration
-------------

Enable debug mode to see extension's test endpoints in backend module:

**Via Site Settings:**

.. code-block:: yaml

   settings:
     simple_rest_api:
       debugMode: true

**Via LocalConfiguration:**

.. code-block:: php

   <?php
   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']['debugMode'] = true;

Version 0.2.1
=============

Released: 09.12.2025

This is a bugfix release that improves multi-language support and adds comprehensive
integration test coverage for all HTTP methods.

Added
-----

* **Comprehensive integration tests** for all HTTP methods

  * GET request tests
  * POST request tests with JSON body
  * PUT request tests for resource updates
  * PATCH request tests for partial updates
  * DELETE request tests
  * Complete test coverage for endpoint resolution

Fixed
-----

* **Language-specific base URI support** for multi-language TYPO3 setups

  * ``ApiRequest`` now correctly uses language-specific base URIs from ``SiteLanguage``
  * Fixed routing for multi-language sites with different base paths (e.g., ``/en/``, ``/de/``)
  * Fixed routing for multi-language sites with different domains (e.g., ``example.com``, ``example.de``)
  * Falls back to site base for backwards compatibility
  * Updated test coverage (7 tests for ApiRequest)

* **PHPStan level 9 compliance** in integration tests

  * Fixed type annotation issues
  * Improved test code quality

* **Code style improvements** for GrumPHP compliance

  * Applied PHP_CodeSniffer fixes
  * Updated code formatting

Technical
---------

* Enhanced ``ApiRequest`` constructor to retrieve and use ``SiteLanguage`` from request
* Removed deprecated Rector rules from configuration

  * Removed strict boolean comparison rules that were causing issues
  * Cleaned up Rector configuration

* All code quality checks passing (PHPStan, Rector, PHPCS, GrumPHP)
* Integration test suite now covers all supported HTTP methods

Version 0.2.0
=============

Released: 03.11.2025

Added
-----

* **Configurable API base path** via TYPO3 Site Set Settings

  * Site Set configuration with ``settings.definitions.yaml``
  * Runtime validation with pattern matching (``^/([a-zA-Z0-9_-]+/)+$``)
  * Support for multi-segment paths (e.g., ``/api/v2/``, ``/rest/v1/``)
  * Dual-layer validation (backend UI + runtime)
  * Comprehensive test coverage for valid and invalid formats
  * Falls back to default ``/api/`` for invalid configurations

* ``ExtensionConfiguration`` class for managing extension settings
* Base path format validation with detailed documentation
* ModifyApiResponseEvent to allow response modifications before returning to client
* Color-coded HTTP method badges in backend module (GET, POST, PUT, PATCH, DELETE)
* Comprehensive quick start guide in README with step-by-step instructions
* Complete HTTP method examples in documentation (all methods now documented)
* Project badges in README (version, license, TYPO3 compatibility)

Changed
-------

* ``ExtensionConfiguration`` made readonly for improved type safety
* Eliminated runtime reflection in parameter resolution
* Renamed ``Parameters`` class to ``EndpointParameterResolver``
* ``ApiEndpointParameter`` now includes ``ServerRequestInterface`` in collection
* Replaced raw arrays with ``ApiEndpointParameterCollection`` for type-safe parameter handling
* Restructured PSR-14 events documentation for improved clarity and navigation
* Replaced 'Adding Request Context' example with more practical 'Loading Extbase Models' example
* Enhanced README with better project overview and usage examples
* Updated all documentation (CLAUDE.md, README.md, RST docs) for configurable base path

Fixed
-----

* Removed reference to non-existent event-flow.svg image in documentation
* String interpolation in tests replaced with concatenation (Rector compliance)

Documentation
-------------

* New documentation section for configurable API base path with examples
* Updated configuration section with Site Set setup instructions
* Added validation requirements and pattern documentation
* Examples of valid and invalid base path formats
* URL examples showing default and custom base paths

Technical
---------

* Added ``pathParameterCount()`` method to ``ApiEndpoint``
* Added date extension to composer-require-checker configuration
* Excluded CLAUDE.md from package distribution via .gitattributes
* Added .phpunit.result.cache and .claude/ to .gitignore
* Removed config folder from repository (site-specific, not for extension)
* Fixed Rector code style issues for PHP 8.2+ compatibility
* All code quality checks passing (PHPStan level 9, Rector, PHPCS)
* Comprehensive test suite: 56 unit tests, 6 integration tests

Version 0.2.0-rc3
=================

Released: 02.11.2025

Changed
-------

* Eliminated runtime reflection in parameter resolution
* Renamed Parameters class to EndpointParameterResolver
* ApiEndpointParameter now includes ServerRequestInterface in collection

Maintenance
-----------

* Added pathParameterCount() method to ApiEndpoint
* Comprehensive test coverage for parameter resolution

Version 0.2.0-rc2
=================

Released: 01.11.2025

Added
-----

* **ModifyApiResponseEvent** - New PSR-14 event to modify API responses before returning to client
* Color-coded HTTP method badges in backend module (GET, POST, PUT, PATCH, DELETE)
* Comprehensive quick start guide in README with step-by-step instructions
* Complete HTTP method examples in documentation (all methods now documented)
* Project badges in README (version, license, TYPO3 compatibility)
* CHANGELOG.md file following Keep a Changelog format

Changed
-------

* Restructured PSR-14 events documentation for improved clarity and navigation
* Replaced 'Adding Request Context' example with more practical 'Loading Extbase Models' example
* Enhanced README with better project overview and usage examples
* Improved backend module visual presentation with color coding

Fixed
-----

* Removed reference to non-existent event-flow.svg image in documentation

Documentation
-------------

* New documentation for ModifyApiResponseEvent with usage examples
* Restructured events documentation with better organization
* Added practical example for loading Extbase models in API endpoints
* Complete coverage of all HTTP methods with code examples

Technical
---------

* Added date extension to composer-require-checker configuration
* Excluded CLAUDE.md from package distribution via .gitattributes
* Added .phpunit.result.cache and .claude/ to .gitignore
* Removed config folder from repository (site-specific, not for extension)
* Fixed Rector code style issues for PHP 8.2+ compatibility

Version 0.2.0-rc1
=================

Released: 31.10.2025

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

Released: 15.01.2025

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

      <?php
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

      <?php
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

   <?php
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
