<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;

final readonly class ApiEndpoint
{
    /**
     * @param class-string $className
     * @param array<string> $tags
     * @param string|null $version API version (e.g., '1', '1.0', '1.2.3')
     */
    public function __construct(
        public string $className,
        public string $method,
        public string $path,
        public string $httpMethod,
        public ApiEndpointParameterCollection $parameters,
        public string $summary = '',
        public string $description = '',
        public array $tags = [],
        public readonly ?string $version = null
    ) {
    }

    public function parameterCount(): int
    {
        return count($this->parameters);
    }

    /**
     * Get the count of path parameters only (excluding ServerRequestInterface).
     */
    public function pathParameterCount(): int
    {
        $count = 0;
        foreach ($this->parameters as $param) {
            if ($param->type !== ServerRequestInterface::class) {
                $count++;
            }
        }

        return $count;
    }

    public function getPathWithoutParameters(): string
    {
        $pathParts = explode('/', trim($this->path, '/'));

        $identifierPathParts = [];

        foreach ($pathParts as $pathPart) {
            if (!preg_match('/{([0-9a-zA-Z]+)}/', $pathPart)) {
                $identifierPathParts[] = $pathPart;
            }
        }

        return '/' . implode('/', $identifierPathParts);
    }

    /**
     * Check if this endpoint has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * Check if this endpoint has any of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAnyTag(array $tags): bool
    {
        foreach ($tags as $tag) {
            if ($this->hasTag($tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this endpoint has all of the given tags.
     *
     * @param array<string> $tags
     */
    public function hasAllTags(array $tags): bool
    {
        foreach ($tags as $tag) {
            if (!$this->hasTag($tag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if this endpoint matches a specific class and method.
     *
     * @param class-string $className
     */
    public function isEndpoint(string $className, string $methodName): bool
    {
        return $this->className === $className && $this->method === $methodName;
    }

    /**
     * Check if this endpoint matches any of the given class/method combinations.
     *
     * @param array<class-string, string|array<string>> $endpoints Format: [ClassName::class => 'methodName'] or [ClassName::class => ['method1', 'method2']] or [ClassName::class => '*']
     */
    public function isAnyEndpoint(array $endpoints): bool
    {
        foreach ($endpoints as $className => $methods) {
            if ($this->className !== $className) {
                continue;
            }

            // Wildcard matches all methods
            if ($methods === '*') {
                return true;
            }

            // Convert single method to array
            $methodList = is_array($methods) ? $methods : [$methods];

            foreach ($methodList as $method) {
                if ($this->method === $method) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if this endpoint's path matches the given pattern.
     * Supports exact match or pattern with wildcards.
     */
    public function matchesPath(string $pathPattern): bool
    {
        return $this->path === $pathPattern;
    }

    /**
     * Get the fully qualified class name.
     *
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->className;
    }

    /**
     * Get the major version number from the version string.
     *
     * @return string|null Major version (e.g., '1' from '1.2.3') or null if unversioned
     */
    public function getMajorVersion(): ?string
    {
        if ($this->version === null) {
            return null;
        }

        return explode('.', $this->version)[0];
    }

    /**
     * Check if this endpoint is versioned.
     */
    public function isVersioned(): bool
    {
        return $this->version !== null;
    }

    /**
     * Check if this endpoint matches a specific version filter.
     *
     * Matching rules:
     * - If filter is '1', matches: '1', '1.0', '1.2', '1.2.3' (major version match)
     * - If filter is '1.2', matches: '1.2' exactly, not '1.0' or '1.2.3'
     * - Unversioned endpoints never match any version filter
     *
     * @param string $versionFilter Version to filter by (e.g., '1', '2.1')
     */
    public function matchesVersion(string $versionFilter): bool
    {
        // Unversioned endpoints don't match any version filter
        if ($this->version === null) {
            return false;
        }

        // Exact match
        if ($this->version === $versionFilter) {
            return true;
        }

        // Major version match (filter '1' matches '1.2.3')
        if (!str_contains($versionFilter, '.')) {
            return $this->getMajorVersion() === $versionFilter;
        }

        // If filter has dots, require exact match (already checked above)
        return false;
    }
}
