<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Service;

use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Value\ApiEndpoint;

/**
 * Service to match API endpoints based on various criteria.
 *
 * This service provides a fluent interface for matching endpoints by:
 * - Class and method names
 * - Tags
 * - Path patterns (with wildcard support)
 * - HTTP methods
 *
 * Example usage:
 * ```php
 * if ($this->matcher->matches($event, [
 *     UserController::class => ['getUser', 'updateUser'],
 *     ProductController::class => '*'
 * ])) {
 *     // Process the event
 * }
 * ```
 */
final readonly class EndpointMatcher
{
    /**
     * Check if an event matches the given criteria.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     * @param array<class-string, string|array<string>> $criteria Format: [ClassName::class => 'methodName'] or [ClassName::class => ['method1', 'method2']] or [ClassName::class => '*']
     */
    public function matches(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        array $criteria
    ): bool {
        $endpoint = $this->getEndpointFromEvent($event);

        foreach ($criteria as $className => $methods) {
            if ($endpoint->getClass() !== $className) {
                continue;
            }

            // Wildcard matches all methods
            if ($methods === '*') {
                return true;
            }

            // Convert single method to array
            $methodList = is_array($methods) ? $methods : [$methods];

            foreach ($methodList as $method) {
                if ($endpoint->method === $method) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an event's endpoint has a specific tag.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     */
    public function hasTag(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        string $tag
    ): bool {
        return $this->getEndpointFromEvent($event)->hasTag($tag);
    }

    /**
     * Check if an event's endpoint has any of the given tags.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     * @param array<string> $tags
     */
    public function hasAnyTag(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        array $tags
    ): bool {
        return $this->getEndpointFromEvent($event)->hasAnyTag($tags);
    }

    /**
     * Check if an event's endpoint has all of the given tags.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     * @param array<string> $tags
     */
    public function hasAllTags(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        array $tags
    ): bool {
        return $this->getEndpointFromEvent($event)->hasAllTags($tags);
    }

    /**
     * Check if an event's endpoint matches a path pattern.
     *
     * Supports wildcards:
     * - /v1/users/* matches /v1/users/123, /v1/users/456/posts, etc.
     * - /v1/* /users matches /v1/admin/users, /v1/public/users, etc.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     */
    public function matchesPath(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        string $pathPattern
    ): bool {
        $endpoint = $this->getEndpointFromEvent($event);

        // Exact match
        if ($endpoint->path === $pathPattern) {
            return true;
        }

        // Wildcard matching
        if (str_contains($pathPattern, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($pathPattern, '/'));
            return (bool)preg_match('/^' . $pattern . '$/', $endpoint->path);
        }

        return false;
    }

    /**
     * Check if an event's endpoint uses a specific HTTP method.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     */
    public function usesHttpMethod(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event,
        string $httpMethod
    ): bool {
        return strtoupper($this->getEndpointFromEvent($event)->httpMethod) === strtoupper($httpMethod);
    }

    /**
     * Extract the ApiEndpoint from the event.
     *
     * @param BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
     */
    private function getEndpointFromEvent(
        BeforeParameterMappingEvent|AfterParameterMappingEvent|ModifyApiResponseEvent $event
    ): ApiEndpoint {
        return match (true) {
            $event instanceof BeforeParameterMappingEvent => $event->getApiEndpoint(),
            $event instanceof AfterParameterMappingEvent => $event->getEndpoint(),
            $event instanceof ModifyApiResponseEvent => $event->getEndpoint(),
        };
    }
}
