<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Event;

use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class AfterParameterMappingEvent
{
    /**
     * @param mixed[] $methodParameters
     */
    public function __construct(
        private array $methodParameters,
        private readonly ApiEndpoint $endpoint,
        private readonly ApiRequest $apiRequest
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getMethodParameters(): array
    {
        return $this->methodParameters;
    }

    public function getEndpoint(): ApiEndpoint
    {
        return $this->endpoint;
    }

    public function getApiRequest(): ApiRequest
    {
        return $this->apiRequest;
    }

    /**
     * @param mixed[] $methodParameters
     */
    public function overrideMethodParameters(array $methodParameters): void
    {
        $this->methodParameters = $methodParameters;
    }
}
