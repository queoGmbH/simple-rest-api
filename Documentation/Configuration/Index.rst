.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

Route Enhancer Setup
=====================

The extension requires a route enhancer to be configured in your site configuration
to handle API routing.

Method 1: Import Configuration File (Recommended)
--------------------------------------------------

Add the following import to your site configuration file
(`config/sites/<your-site>/config.yaml`):

.. code-block:: yaml

   imports:
     - { resource: "EXT:simple_rest_api/Configuration/Yaml/RouteEnhancer.yaml" }

This imports the pre-configured route enhancer settings.

Method 2: Manual Configuration
-------------------------------

Alternatively, add the route enhancer manually to your site configuration:

.. code-block:: yaml

   routeEnhancers:
     SimpleRestApiEnhancer:
       type: SimpleRestApiEnhancer

What This Does
--------------

The route enhancer:

* Registers a configurable base path for all API endpoints (default: `/api/`)
* Routes all requests under the base path to the Simple REST API middleware
* Handles URL parameter mapping from the path to your endpoint methods

Customizing API Base Path
==========================

By default, all API endpoints are accessible under the `/api/` base path. You can
customize this globally via extension configuration.

Extension Manager (Recommended)
--------------------------------

1. Go to **Admin Tools** → **Settings** → **Extension Configuration**
2. Select ``simple_rest_api``
3. Configure:

   * **API Base Path**: The base path for all REST API endpoints (default: ``/api/``)
   * **Debug Mode**: Enable to show extension's own example/test endpoints in backend module

LocalConfiguration.php
----------------------

Alternatively, configure directly in your LocalConfiguration.php or AdditionalConfiguration.php:

.. code-block:: php

   <?php
   // In typo3conf/LocalConfiguration.php or AdditionalConfiguration.php
   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
       'basePath' => '/rest/',    // Custom base path
       'debugMode' => false,      // Hide extension's test endpoints
   ];

Valid Base Paths
----------------

The base path must follow these requirements:

* Must start and end with a forward slash
* Only letters, numbers, hyphens, and underscores are allowed
* Pattern: ``^/[a-zA-Z0-9_-]*/$``

**Examples of valid base paths:**

* ``/api/`` (default)
* ``/rest/``
* ``/services/``
* ``/api/v2/``
* ``/my-api/``

**URL Examples:**

With default base path:

* Endpoint path: ``/v1/users``
* Full URL: ``https://example.com/api/v1/users``

With custom base path ``/rest/``:

* Endpoint path: ``/v1/users``
* Full URL: ``https://example.com/rest/v1/users``

Debug Mode Configuration
========================

The extension includes example/test endpoints in its own namespace
(``Queo\SimpleRestApi\*``). By default, these are hidden in production to keep
the backend module clean.

What Debug Mode Does
--------------------

When **debug mode is disabled** (default):

* Extension's own test endpoints are **hidden** in the backend module
* Only your application's endpoints are visible
* Recommended for production environments

When **debug mode is enabled**:

* Extension's test endpoints are **visible** in the backend module
* Useful for development and testing
* Shows example endpoints like ``GET /my-get-endpoint``, ``POST /my-post-endpoint``
* Helps understand how the extension works

Configuration Methods
---------------------

Extension Manager (Recommended)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Go to **Admin Tools** → **Settings** → **Extension Configuration**
2. Select ``simple_rest_api``
3. Set **Debug Mode** to:

   * ``0`` or unchecked = Disabled (hide test endpoints) - **Default**
   * ``1`` or checked = Enabled (show test endpoints)

LocalConfiguration.php
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

   <?php
   // In typo3conf/LocalConfiguration.php or AdditionalConfiguration.php
   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
       'basePath' => '/api/',
       'debugMode' => false,  // or true to enable
   ];

Best Practices
--------------

* **Production**: Keep debug mode **disabled** (``false``)
* **Development**: Enable debug mode to see example endpoints
* **Testing**: Enable temporarily to verify the extension is working
* **Documentation**: The test endpoints serve as working examples of the extension's features

Test Endpoints Included
------------------------

When debug mode is enabled, these example endpoints become visible:

* ``GET /my-get-endpoint`` - Basic GET example
* ``POST /my-post-endpoint`` - POST example
* ``GET /my-param-endpoint/{param1}/{param2}`` - URL parameters example
* ``PUT /resources/{resourceId}`` - PUT example
* ``PATCH /resources/{resourceId}`` - PATCH example
* ``DELETE /resources/{resourceId}`` - DELETE example

These endpoints are defined in ``Classes/Controller/TestController.php`` and serve
as reference implementations.

Backend Module Access
=====================

The backend module is automatically available after installation under:

**Site** → **REST API Endpoints**

Access Control
--------------

By default, the module is accessible to administrators only. This is configured
in `Configuration/Backend/Modules.php`:

.. code-block:: php

   <?php
   // In Configuration/Backend/Modules.php
   return [
       // ...
       'access' => 'admin',
       // ...
   ];

To grant access to other user groups, modify this setting or adjust permissions
in the TYPO3 backend user management.

Cache Configuration
===================

The extension automatically configures a cache for API endpoints:

.. code-block:: php

   <?php
   // Automatically configured by the extension
   $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['somple_rest_api'] = [
       'frontend' => VariableFrontend::class,
       'backend' => FileBackend::class,
       'options' => [
           'defaultLifetime' => 3600 * 24 // 1 day
       ],
       'groups' => ['system']
   ];

This cache is used internally and generally doesn't require modification.

Clear Cache
-----------

If you modify API endpoints, clear the system cache:

.. code-block:: bash

   vendor/bin/typo3 cache:flush

Or in the TYPO3 backend: **Admin Tools** → **Flush TYPO3 and PHP Cache**

Advanced Configuration
======================

Service Container Configuration
--------------------------------

All API endpoints are automatically registered in TYPO3's dependency injection
container via the `AsApiEndpoint` attribute. The registration happens through
`Classes/DependencyInjection/ApiEndpointProviderPass.php`.

Custom Middleware
-----------------

If you need to add custom middleware for your API endpoints, you can register
it before or after the `simple-rest-api/api-resolver` middleware in your
`Configuration/RequestMiddlewares.php`:

.. code-block:: php

   <?php
   // In Configuration/RequestMiddlewares.php
   return [
       'frontend' => [
           'my-extension/custom-api-middleware' => [
               'target' => \MyVendor\MyExtension\Middleware\CustomApiMiddleware::class,
               'before' => [
                   'simple-rest-api/api-resolver',
               ],
           ],
       ],
   ];

Configuration Checklist
=======================

After installation, verify these configuration steps:

* ☐ Route enhancer configured in site configuration
* ☐ API base path configured via Extension Manager (optional, defaults to ``/api/``)
* ☐ Debug mode configured if needed (optional, defaults to disabled)
* ☐ System cache cleared
* ☐ Backend module accessible under **Site** → **REST API Endpoints**
* ☐ Test endpoint created and visible in backend module

Troubleshooting Configuration
==============================

If endpoints aren't working:

1. **Clear all caches** - System cache must be cleared after changes
2. **Verify route enhancer** - Check site configuration YAML syntax
3. **Check backend module** - Verify your endpoints appear in the list
4. **Test URL directly** - Try accessing `/api/` to ensure routing works
5. **Check web server** - Ensure `.htaccess` or nginx config allows API paths

See :ref:`known-problems` for more troubleshooting tips.
