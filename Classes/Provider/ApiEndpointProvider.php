<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Provider;

use Psr\Container\ContainerInterface;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class ApiEndpointProvider
{
    private array $endpoints = [];

    public function addEndpoint(string $className, string $methodName, string $httpMethod, string $path): void
    {
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

        $endpoint = new ApiEndpoint($className, $methodName, $path, $httpMethod, $parameterNames);
        $this->endpoints[$this->getIdentifier($httpMethod, $identifierPath)] = $endpoint;
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

    private function getIdentifier(string $httpMethod, string $path): string
    {
        return $httpMethod . '_' . $path;
    }
}
