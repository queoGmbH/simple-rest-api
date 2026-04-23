.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

What does it do?
================

The Simple REST API extension provides a lightweight framework for creating
REST API endpoints in TYPO3 using PHP 8 attributes. It focuses on simplicity
and ease of use, handling the routing and basic parameter mapping while giving
developers full control over the implementation.

Key Features
============

* **Simple method configuration** - Mark any method as an API endpoint using the
  `#[AsApiEndpoint]` PHP attribute
* **Automatic routing** - All endpoints are automatically routed under a configurable base path (default: ``/api/``)
* **Parameter handling** - Scalar parameters from the URL path are automatically
  mapped to method arguments
* **ServerRequest support** - Access the full PSR-7 ServerRequest for complex scenarios
* **Parameter events** - Hooks to adjust parameters before they reach your endpoint
* **Backend module** - Visual overview of all registered endpoints with documentation
* **Type-safe** - Full PHP 8.2+ type safety with reflection-based parameter extraction
* **Well-documented** - Parameters can be documented using PHPDoc for automatic
  API documentation generation

Philosophy
==========

This extension intentionally keeps things simple and doesn't try to solve every
API-related problem. It focuses specifically on:

* Routing requests to PHP methods
* Mapping URL path parameters to method arguments
* Providing a clean integration with TYPO3's routing system

What it **doesn't** do:

* Complex serialization/deserialization
* Authentication and authorization (use TYPO3's built-in mechanisms or add your own)
* Request validation (implement in your endpoints)
* Response formatting beyond basic JSON (use TYPO3's response classes)
* Rate limiting, CORS, etc. (add as needed for your use case)

This philosophy allows the extension to remain simple, maintainable, and flexible
for various use cases.

Use Cases
=========

This extension is perfect for:

* Creating simple API endpoints for AJAX requests in TYPO3 backends
* Building custom REST APIs for mobile apps or SPAs
* Integrating TYPO3 with external services
* Rapid prototyping of API endpoints
* Projects where you want full control over API implementation

Requirements
============

* TYPO3 13.4 or 14.0 or higher
* PHP 8.2 or higher

Backend Module
==============

The extension includes a backend module under **Site** → **REST API Endpoints**
that provides a comprehensive overview of all registered API endpoints.

The module displays for each endpoint:

* HTTP method (GET, POST, PUT, DELETE, etc.) with color-coded badges
* Full endpoint path
* Summary and description (if provided)
* Collapsible details showing parameter information
* Parameter table with name, type, and description

This visual overview makes it easy to see all available API endpoints in your
TYPO3 installation at a glance.
