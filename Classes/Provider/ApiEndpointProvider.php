<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Provider;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class ApiEndpointProvider
{
    private array $endpoints = [];

    public function addEndpoint(
        string $className,
        string $methodName,
        string $httpMethod,
        string $path,
        string $summary = '',
        string $description = ''
    ): void {
        $pathParts = explode('/', trim($path, '/'));

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

        $endpoint = new ApiEndpoint($className, $methodName, $path, $httpMethod, $parameterNames, $summary, $description, $parameters);
        $this->endpoints[$this->getIdentifier($httpMethod, $identifierPath)] = $endpoint;
    }

    /**
     * @param array<string> $parameterNames
     * @return array<ApiEndpointParameter>
     */
    private function extractParameterInformation(string $className, string $methodName, array $parameterNames): array
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

                // Skip ServerRequestInterface parameters
                $paramType = $reflectionParameter->getType();
                if ($paramType instanceof ReflectionNamedType && $paramType->getName() === ServerRequestInterface::class) {
                    continue;
                }

                // Only include parameters that are in the path
                if (in_array($paramName, $parameterNames, true)) {
                    $type = $this->getParameterType($reflectionParameter);
                    $description = $paramDescriptions[$paramName] ?? '';

                    $parameters[] = new ApiEndpointParameter($paramName, $type, $description);
                }
            }
        } catch (\ReflectionException $e) {
            // If reflection fails, return empty array
            return [];
        }

        return $parameters;
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

    private function getParameterType(\ReflectionParameter $parameter): string
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
                if ($endpoint instanceof ApiEndpoint && $endpoint->parameterCount() === $undetectedPathPartCount) {
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
}
