<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Middleware;

use ReflectionMethod;
use ReflectionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Registry\EndpointRegistry;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiPath;
use RuntimeException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiResolverMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ApiEndpointProvider $endpointProvider,
        private readonly ExtensionConfiguration $extensionConfiguration
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');

        $apiPath = new ApiPath($site->getBase(), $request->getUri(), $this->extensionConfiguration->getApiBasePath());

        // Check whether it is an API path (optional, for path prefix)
        if (!$apiPath->isApiPath()) {
            return $handler->handle($request);
        }

        $endpoint = $this->endpointProvider->getEndpoint($request->getMethod(), $apiPath->getEndpointPath());

        if ($endpoint instanceof ApiEndpoint) {
            // Create controller instance
            /** @var object $controller */
            $controller = GeneralUtility::makeInstance($endpoint->className);
            $methodName = $endpoint->method;

            $parameters = [];

            // @todo: Move this somewhere else
            if ($endpoint->parameterCount() > 0) {
                $parameters = $apiPath->getParameterValuesFromPath($endpoint->parameterCount());
                $reflectionMethod = new ReflectionMethod($endpoint->className, $methodName);
                $reflectionParams = $reflectionMethod->getParameters();

                foreach ($reflectionParams as $key => $reflectionParam) {
                    $type = $reflectionParam->getType()->getName();

                    $parameters[$key] = match ($type) {
                        'int' => (int)$parameters[$key],
                        'string' => (string)$parameters[$key],
                        'float' => (float)$parameters[$key],
                        'bool' => (bool)$parameters[$key],
                        default => $parameters[$key],
                    };
                }
            }

            // Call method with parameters
            $result = $controller->$methodName(...$parameters);

            // If the result is already a response, return it.
            if ($result instanceof ResponseInterface) {
                return $result;
            }

            throw new RuntimeException('Your controller ' . $endpoint->className . ' method ' . $endpoint->method . ' has to return a ResponseInterface!', 4976710617);
        }

        // If no endpoint was found, forward request
        return $handler->handle($request);
    }
}
