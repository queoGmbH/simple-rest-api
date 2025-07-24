<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Middleware;

use Queo\SimpleRestApi\Http\ApiRequest;
use ReflectionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use RuntimeException;
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
        /** @var ApiRequest $apiRequest */
        $apiRequest = GeneralUtility::makeInstance(ApiRequest::class, $request, $this->extensionConfiguration);

        // Check whether it is an API path (optional, for path prefix)
        if (!$apiRequest->isApiRequest()) {
            return $handler->handle($request);
        }

        $endpoint = $this->endpointProvider->getEndpoint($apiRequest);

        if ($endpoint instanceof ApiEndpoint) {
            // Create controller instance
            /** @var object $className */
            $className = GeneralUtility::makeInstance($endpoint->className);
            $methodName = $endpoint->method;

            // @todo: Add event before an after parameter mapping to give other developers the possibility to adjust parameters
            $methodParameters = $apiRequest->getParameters($endpoint)->buildMethodParameters($className::class, $methodName);

            // Call method with parameters
            $result = $className->$methodName(...$methodParameters);

            // If the result is already a response, return it.
            if ($result instanceof ResponseInterface) {
                return $result;
            }

            throw new RuntimeException(
                'Your controller ' . $endpoint->className
                . ' method ' . $endpoint->method
                . ' has to return a ResponseInterface!',
                4976710617
            );
        }

        // If no endpoint was found, forward request
        return $handler->handle($request);
    }
}
