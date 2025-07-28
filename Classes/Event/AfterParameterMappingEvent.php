<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Event;

use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class AfterParameterMappingEvent
{
    public function __construct(
        private Parameters $parameters,
        private readonly ApiEndpoint $endpoint,
        private readonly ApiRequest $apiRequest
    ) {
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function getEndpoint(): ApiEndpoint
    {
        return $this->endpoint;
    }

    public function getApiRequest(): ApiRequest
    {
        return $this->apiRequest;
    }

    public function overrideMethodParameters(Parameters $parameters): void
    {
        $this->parameters = $parameters;
    }
}
