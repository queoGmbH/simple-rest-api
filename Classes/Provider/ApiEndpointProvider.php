<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Provider;

use InvalidArgumentException;
use ReflectionException;
use ReflectionParameter;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use ReflectionClass;
use ReflectionNamedType;

final class ApiEndpointProvider
{
    private array $endpoints = [];

    /**
     * @param class-string  $className
     * @param array<string> $tags
     * @param string|null $version API version (e.g., '1', '1.0', '1.2.3'). If specified, path will be automatically prefixed with /v{major}/
     */
    public function addEndpoint(
        string $className,
        string $methodName,
        string $httpMethod,
        string $path,
        string $summary = '',
        string $description = '',
        array $tags = [],
        ?string $version = null
    ): void {
        // Validate: Prevent manual version prefixes in path
        if ($version !== null && preg_match('#^/v\d+/#', $path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path "%s" should not contain version prefix (e.g., /v1/). ' .
                    'The version "%s" is specified in the attribute and will be automatically prefixed. ' .
                    'Remove the version from the path. (Controller: %s::%s())',
                    $path,
                    $version,
                    $className,
                    $methodName
                ),
                1733918400
            );
        }

        // Auto-prefix path with version if specified
        $versionedPath = $this->buildVersionedPath($path, $version);

        $pathParts = explode('/', trim($versionedPath, '/'));

        $identifierPathParts = [];
        $parameterNames = [];
        $parameterPosition = 0;

        foreach ($pathParts as $pathPart) {
            if (!preg_match('/{([0-9a-zA-Z]+)}/', $pathPart, $matches)) {
                $identifierPathParts[] = $pathPart;
            } else {
                $parameterNames[$parameterPosition++] = $matches[1];
            }
        }

        $identifierPath = '/' . implode('/', $identifierPathParts);

        // Extract detailed parameter information via reflection
        $parameters = $this->extractParameterInformation($className, $methodName, $parameterNames);

        $endpoint = new ApiEndpoint($className, $methodName, $versionedPath, $httpMethod, $parameters, $summary, $description, $tags, $version);
        $this->endpoints[$this->getIdentifier($httpMethod, $identifierPath)] = $endpoint;
    }

    /**
     * @param  class-string  $className
     * @param  array<string> $parameterNames
     */
    private function extractParameterInformation(string $className, string $methodName, array $parameterNames): ApiEndpointParameterCollection
    {
        $parameters = [];

        try {
            $reflectionClass = new ReflectionClass($className);
            $reflectionMethod = $reflectionClass->getMethod($methodName);

            // Parse PHPDoc for parameter descriptions
            $docComment = $reflectionMethod->getDocComment();
            $paramDescriptions = $this->parseParamDescriptions($docComment ?: '');

            // Get method parameters from reflection
            $methodParameters = $reflectionMethod->getParameters();

            foreach ($methodParameters as $reflectionParameter) {
                $paramName = $reflectionParameter->getName();
                $paramType = $reflectionParameter->getType();

                // Include ServerRequestInterface parameters (they're not from the path)
                if ($paramType instanceof ReflectionNamedType && $paramType->getName() === ServerRequestInterface::class) {
                    $description = $paramDescriptions[$paramName] ?? '';
                    $parameters[] = new ApiEndpointParameter($paramName, ServerRequestInterface::class, $description);
                    continue;
                }

                // Only include parameters that are in the path
                if (in_array($paramName, $parameterNames, true)) {
                    $type = $this->getParameterType($reflectionParameter);
                    $description = $paramDescriptions[$paramName] ?? '';

                    $parameters[] = new ApiEndpointParameter($paramName, $type, $description);
                }
            }
        } catch (ReflectionException) {
            // If reflection fails, create basic parameter objects from path parameter names
            foreach ($parameterNames as $paramName) {
                $parameters[] = new ApiEndpointParameter($paramName);
            }
        }

        return new ApiEndpointParameterCollection(...$parameters);
    }

    /**
     * @return array<string, string>
     */
    private function parseParamDescriptions(string $docComment): array
    {
        $descriptions = [];

        if (preg_match_all('/@param\s+(\S+)\s+\$(\w+)\s+(.*)$/m', $docComment, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $paramName = $match[2];
                $description = trim($match[3]);
                $descriptions[$paramName] = $description;
            }
        }

        return $descriptions;
    }

    private function getParameterType(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        if ($type === null) {
            return 'mixed';
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        return 'mixed';
    }

    public function getEndpoint(ApiRequestInterface $apiRequest): ?ApiEndpoint
    {
        $endpoint = $this->endpoints[$this->getIdentifier($apiRequest->getHttpMethod(), $apiRequest->getEndpointPath())] ?? null;

        if (!$endpoint instanceof ApiEndpoint) {
            $pathParts = explode('/', trim($apiRequest->getEndpointPath(), '/'));
            $undetectedPathPartCount = count($pathParts);

            $checkEndpointPath = '';

            foreach ($pathParts as $pathPart) {
                $checkEndpointPath .= '/' . $pathPart;
                $endpoint = $this->endpoints[$this->getIdentifier($apiRequest->getHttpMethod(), $checkEndpointPath)] ?? null;
                $undetectedPathPartCount--;
                if ($endpoint instanceof ApiEndpoint && $endpoint->pathParameterCount() === $undetectedPathPartCount) {
                    break;
                }
            }
        }

        return $endpoint;
    }

    /**
     * @return array<ApiEndpoint>
     */
    public function getAllEndpoints(): array
    {
        return array_values($this->endpoints);
    }

    private function getIdentifier(string $httpMethod, string $path): string
    {
        return $httpMethod . '_' . $path;
    }

    /**
     * Build a versioned path by prefixing with /v{major}/ if version is specified.
     *
     * Examples:
     * - buildVersionedPath('/users', null) → '/users'
     * - buildVersionedPath('/users', '1') → '/v1/users'
     * - buildVersionedPath('/users', '1.2.3') → '/v1/users'
     *
     * @param string $path Original path from attribute
     * @param string|null $version Version string (e.g., '1', '1.0', '1.2.3')
     * @return string Versioned path or original if no version
     */
    private function buildVersionedPath(string $path, ?string $version): string
    {
        if ($version === null) {
            return $path;
        }

        // Extract major version (everything before first dot)
        $majorVersion = explode('.', $version)[0];

        // Ensure path starts with /
        $normalizedPath = '/' . ltrim($path, '/');

        // Prefix with /v{major}
        return '/v' . $majorVersion . $normalizedPath;
    }
}
