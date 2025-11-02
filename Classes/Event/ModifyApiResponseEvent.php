<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Event;

use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;

/**
 * Event that is dispatched after the API endpoint method has been invoked
 * and before the response is returned to the client.
 *
 * This event allows listeners to modify the response, add headers, change
 * status codes, or perform any other response modifications.
 *
 * Common use cases:
 * - Adding CORS headers
 * - Adding custom headers (e.g., X-API-Version, X-Request-ID)
 * - Modifying response content
 * - Adding caching headers
 * - Logging response details
 */
final class ModifyApiResponseEvent
{
    public function __construct(
        private ResponseInterface $response,
        private readonly ApiEndpoint $endpoint,
        private readonly ApiRequestInterface $apiRequest
    ) {
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getEndpoint(): ApiEndpoint
    {
        return $this->endpoint;
    }

    public function getApiRequest(): ApiRequestInterface
    {
        return $this->apiRequest;
    }

    /**
     * Override the response that will be returned to the client.
     *
     * This allows complete control over the response, including:
     * - Changing the response body
     * - Modifying headers
     * - Changing the status code
     * - Replacing the entire response object
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
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
