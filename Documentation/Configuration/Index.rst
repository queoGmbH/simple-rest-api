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
customize this per site using TYPO3 Site Set Settings.

Using Site Sets (Recommended)
------------------------------

Add the Simple REST API site set and configure the base path in your site
configuration (`config/sites/<your-site>/config.yaml`):

.. code-block:: yaml

   sets:
     - simple_rest_api/main

   settings:
     simple_rest_api:
       basePath: '/rest/'  # Customize to your needs

Direct Configuration
--------------------

Alternatively, configure the base path directly without using the site set:

.. code-block:: yaml

   settings:
     simple_rest_api:
       basePath: '/services/'

Valid Base Paths
----------------

The base path must follow these requirements:

* Must start and end with a forward slash
* Each path segment must contain only letters, numbers, hyphens, and underscores
* Must have at least one path segment (e.g. ``/api/``)
* Supports multi-segment paths (e.g. ``/api/v2/``)
* Pattern: ``^\/([a-zA-Z0-9_-]+\/)+$``

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

Debug Mode
==========

By default, the extension hides its own internal endpoints (from the
``Queo\SimpleRestApi\`` namespace) in the backend module. This prevents
example or test controllers bundled with the extension from cluttering the
endpoint list in production installations.

To show all endpoints including the extension's own, enable debug mode:

**Via Site Settings:**

.. code-block:: yaml

   settings:
     simple_rest_api:
       debugMode: true

**Via LocalConfiguration:**

.. code-block:: php

   <?php
   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']['debugMode'] = true;

.. note::
   Debug mode only affects the backend module display. It does not expose
   additional routes or change any API behavior.

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
   $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['simple_rest_api'] = [
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
it before or after the ``queo/simple-rest-api/api-resolver-middleware`` middleware
in your ``Configuration/RequestMiddlewares.php``:

.. code-block:: php

   <?php
   // In Configuration/RequestMiddlewares.php
   return [
       'frontend' => [
           'my-extension/custom-api-middleware' => [
               'target' => \MyVendor\MyExtension\Middleware\CustomApiMiddleware::class,
               'before' => [
                   'queo/simple-rest-api/api-resolver-middleware',
               ],
           ],
       ],
   ];

Configuration Checklist
=======================

After installation, verify these configuration steps:

* ☐ Route enhancer configured in site configuration
* ☐ API base path customized (optional, defaults to `/api/`)
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
