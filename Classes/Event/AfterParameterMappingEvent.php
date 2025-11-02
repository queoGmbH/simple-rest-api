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

    /**
     * Check if the current endpoint matches a specific class and method.
     *
     * @param class-string $className
     */
    public function isEndpoint(string $className, string $methodName): bool
    {
        return $this->endpoint->isEndpoint($className, $methodName);
    }

    /**
     * Check if the current endpoint has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return $this->endpoint->hasTag($tag);
    }

    /**
     * Check if the current endpoint has any of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAnyTag(array $tags): bool
    {
        return $this->endpoint->hasAnyTag($tags);
    }

    /**
     * Check if the current endpoint has all of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAllTags(array $tags): bool
    {
        return $this->endpoint->hasAllTags($tags);
    }

    /**
     * Check if the current endpoint's path matches the given pattern.
     */
    public function matchesPath(string $pathPattern): bool
    {
        return $this->endpoint->matchesPath($pathPattern);
    }
}
