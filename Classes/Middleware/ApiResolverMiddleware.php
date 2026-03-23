<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Http\ApiRequest;
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
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

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
            $className = GeneralUtility::makeInstance($endpoint->className);
            $methodName = $endpoint->method;
            $pathParameters = $apiRequest->getParameters($endpoint);

            /** @var BeforeParameterMappingEvent $event */
            $event = $this->eventDispatcher->dispatch(new BeforeParameterMappingEvent($pathParameters, $endpoint, $apiRequest));
            $pathParameters = $event->getPathParameters();

            $methodParameters = $pathParameters->buildMethodParameters();

            /** @var AfterParameterMappingEvent $event */
            $event = $this->eventDispatcher->dispatch(new AfterParameterMappingEvent($methodParameters, $endpoint, $apiRequest));
            $methodParameters = $event->getMethodParameters();

            // Call method with parameters
            $result = $className->$methodName(...$methodParameters);

            // If the result is already a response, dispatch event to allow modifications
            if ($result instanceof ResponseInterface) {
                /** @var ModifyApiResponseEvent $event */
                $event = $this->eventDispatcher->dispatch(
                    new ModifyApiResponseEvent($result, $endpoint, $apiRequest)
                );

                return $event->getResponse();
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
