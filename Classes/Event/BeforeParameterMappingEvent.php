<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Event;

use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class BeforeParameterMappingEvent
{
    public function __construct(
        private Parameters $pathParameters,
        private readonly ApiEndpoint $apiEndpoint,
        private readonly ApiRequest $apiRequest
    ) {
    }

    public function getPathParameters(): Parameters
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

    public function overrideParameters(Parameters $pathParameters): void
    {
        $this->pathParameters = $pathParameters;
    }

    /**
     * Check if the current endpoint matches a specific class and method.
     *
     * @param class-string $className
     */
    public function isEndpoint(string $className, string $methodName): bool
    {
        return $this->apiEndpoint->isEndpoint($className, $methodName);
    }

    /**
     * Check if the current endpoint has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return $this->apiEndpoint->hasTag($tag);
    }

    /**
     * Check if the current endpoint has any of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAnyTag(array $tags): bool
    {
        return $this->apiEndpoint->hasAnyTag($tags);
    }

    /**
     * Check if the current endpoint has all of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAllTags(array $tags): bool
    {
        return $this->apiEndpoint->hasAllTags($tags);
    }

    /**
     * Check if the current endpoint's path matches the given pattern.
     */
    public function matchesPath(string $pathPattern): bool
    {
        return $this->apiEndpoint->matchesPath($pathPattern);
    }
}
