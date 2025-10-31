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

* Registers the `/api/` base path for all API endpoints
* Routes all requests under `/api/*` to the Simple REST API middleware
* Handles URL parameter mapping from the path to your endpoint methods

Backend Module Access
=====================

The backend module is automatically available after installation under:

**Site** → **REST API Endpoints**

Access Control
--------------

By default, the module is accessible to administrators only. This is configured
in `Configuration/Backend/Modules.php`:

.. code-block:: php

   'access' => 'admin',

To grant access to other user groups, modify this setting or adjust permissions
in the TYPO3 backend user management.

Cache Configuration
===================

The extension automatically configures a cache for API endpoints:

.. code-block:: php

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
