<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Event;

use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class BeforeParameterMappingEvent
{
    public function __construct(
        private EndpointParameterResolver $pathParameters,
        private readonly ApiEndpoint $apiEndpoint,
        private readonly ApiRequest $apiRequest
    ) {
    }

    public function getPathParameters(): EndpointParameterResolver
    {
        return $this->pathParameters;
    }

    public function getApiEndpoint(): ApiEndpoint
    {
        return $this->apiEndpoint;
    }

    public function getApiRequest(): ApiRequest
    {
        return $this->apiRequest;
    }

    public function overrideParameters(EndpointParameterResolver $pathParameters): void
    {
        $this->pathParameters = $pathParameters;
    }
}
