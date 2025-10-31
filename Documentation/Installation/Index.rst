.. include:: /Includes.rst.txt

.. _installation:

============
Installation
============

Composer Installation (Recommended)
====================================

Install the extension using Composer:

.. code-block:: bash

   composer require queo/simple-rest-api

After installation, activate the extension via CLI:

.. code-block:: bash

   vendor/bin/typo3 extension:activate simple_rest_api

Verify Installation
===================

After installation, you should see:

1. A new backend module under **Site** → **REST API Endpoints**
2. The extension listed in the Extension Manager
3. The route enhancer available in your site configuration

Next Steps
==========

After installation, proceed to :ref:`configuration` to set up the route enhancer
and start creating your first API endpoints.
