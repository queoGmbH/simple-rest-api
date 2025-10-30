# EXT: simple_rest_api - Simple REST API for TYPO3 provides simple endpoint configuration for api requests

**Compatible with TYPO3 11.5 LTS and PHP 8.0/8.1**

## Features

* Simple method configuration as api endpoint via `AsApiEndpoint` attribute.
* Handling of scalar parameters as method argument.
* Handling of `ServerRequestInterface` implementation as method parameter.
* Events to adjust parameters before handing over to api method.

## Why another api extension?

This extension does not handle a lot - it just handles routing and simple parameters from the url. Everything else
has to be done by the developer. But this keeps it simple ;-).


## Installation

```sh
composer req queo/simple-rest-api
```

Once installed, you can configure methods via `AsApiEndpoint` attribute as API endpoints. See usage section.

## Usage

### Quick start guide

#### 1. Configure route enhancer

To get the api working the route enhancer needs to be configured for your project. Either you import the file
`Configuration/Yaml/RouteEnhancer.yaml` from this extension in your site configuration or put the following code
directly into it:

```yaml
routeEnhancers:
  SimpleRestApiEnhancer:
    type: SimpleRestApiEnhancer
```

#### 2. Create a class with a method for your endpoint.

```php
<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class MyApiController
{
    public function myApiEndpoint(): ResponseInterface
    {
        return new JsonResponse(['success' => true]);
    }
}
```

#### 3. Select an http method (GET, POST, ...) and think of an api endpoint path (configure it WITHOUT api base path '/api/'!)

```php
<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

final class MyApiController
{
    #[AsApiEndpoint(method: 'GET', path: '/v1/my-api-endpoint')]
    public function myApiEndpoint(): ResponseInterface
    {
        return new JsonResponse(['success' => true]);
    }
}
```

After cache clearing your api endpoint should be reachable via https://example.com/api/v1/my-api-endpoint

#### 4. Add some simple scalar parameters to your path. Objects (like Extbase MVC domain object) are not respected!

```php
<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

final class MyApiController
{
    #[AsApiEndpoint(method: 'GET', path: '/v1/my-api-endpoint/{param1}/{param2}')]
    public function myApiEndpoint(int $param1, string $param2): ResponseInterface
    {
        return new JsonResponse(
            [
                'success' => true,
                'parameters' => [
                    'param1' => $param1,
                    'param2' => $param2
                ]
            ]
        );
    }
}
```

Test your api endpoint with https://example.com/api/v1/my-api-endpoint/123/my-string.

#### 5. You need the middleware request object in your endpoint? Just add it as method parameter

```php
<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

final class MyApiController
{
    #[AsApiEndpoint(method: 'GET', path: '/v1/my-api-endpoint/{param1}/{param2}')]
    public function myApiEndpoint(int $param1, string $param2, ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(
            [
                'success' => true,
                'parameters' => [
                    'param1' => $param1,
                    'param2' => $param2,
                    'requestUri' => (string)$request->getUri()
                ]
            ]
        );
    }
}
```

Test your api endpoint with https://example.com/api/v1/my-api-endpoint/123/my-string.

### Events

#### BeforeParameterMappingEvent / AfterParameterMappingEvent

These events can be used to manipulate the parameters from url path on its way to your api endpoint method.

##### BeforeParameterMappingEvent

Use this event to manipulate the parameters before the path values are mapped. If you want to map some extbase
domain object

```php
<?php

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Collection\Parameters;
use Vendor\MyExtension\Domain\Repository\MyRepository;

class DomainObjectEventListener
{

    #[AsEventListener(identifier: 'my-extension/modify-api-request-parameters')]
    public function __invoke(BeforeParameterMappingEvent $event)
    {
        $parameters = $event->getPathParameters();
        
        $newPathParameterValues = [];

        foreach ($parameters->endpointParameterNames as $key => $parameterName) {
            if ($parameterName === 'myDomainObjectParam') {
                $repository = GeneralUtility::makeInstance(MyRepository::class)
                
                $uid = (int)$parameters->pathParameterValues[$key];
                $myDomainObject = $repository->findByUid($uid);
                
                $newParameters = $parameters->withNewParameterValue($key, $myDomainObject);
                $event->overrideParameters($newParameters);
                break;
            }
        }
    }
}
```
**TBD**

## Open features/topics

* Allow to configure the api base path in site configuration.
* Test and make work all HTTP methods.
* Make parameter events working, add tests for them and document their functionality.
* Documentation for TYPO3 extension repository.
