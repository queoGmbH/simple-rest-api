# EXT: simple_rest_api - Simple REST API for TYPO3 provides simple endpoint configuration for api requests

## Features

* Simple method configuration as api endpoint via `AsApiEndpoint` attribute.
* Handling of scalar parameters as method argument.
* Handling of `ServerRequestInterface` implementation as method parameter.
* Events to adjust parameters before handing over to api method.

## Why another api extension

This extension does not handle a lot - it just handles routing and simple parameters from the url. Everything else
has to be done by the developer. But this keeps it simple ;-).


## Installation

```sh
composer req queo/simple-rest-api
```

Once installed, you can configure methods via `AsApiEndpoint` attribute as API endpoints. See usage section.

## Usage

Some example how to use the extension's attribute to configure an endpoint.

```php
<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class TestController
{
    #[AsApiEndpoint(method: 'GET', '/v1/my-api-endpoint/{someValue}')]
    public function myApiEndpoint(int $someValue, ServerRequestInterface $request): ResponseInterface
    {
        // Your code ...
        return new JsonResponse(['success' => true]);
    }
}
```

Clear cache!

Now you can access this endpoint via https://my-domain.com/api/v1/my-api-endpoint/123
