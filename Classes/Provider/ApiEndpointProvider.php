<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Provider;

use Psr\Container\ContainerInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final class ApiEndpointProvider
{
    protected ContainerInterface $container;

    /**
     * @var array
     */
    protected array $endpoints = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addEndpoint(string $className, string $methodName, string $httpMethod, string $path): void
    {
        $pathParts = explode('/', trim($path, '/'));

        $identifierPathParts = [];
        $parameterNames = [];
        $parameterPosition = 1;

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

    public function getEndpoint(string $httpMethod, string $incomePath): ?ApiEndpoint
    {
        $endpoint = $this->endpoints[$this->getIdentifier($httpMethod, $incomePath)] ?? null;

        if (!$endpoint instanceof ApiEndpoint) {
            $pathParts = explode('/', trim($incomePath, '/'));
            $undetectedPathPartCount = count($pathParts);

            $checkEndpointPath = '';

            foreach ($pathParts as $pathPart) {
                $checkEndpointPath .= '/' . $pathPart;
                $endpoint = $this->endpoints[$this->getIdentifier($httpMethod, $checkEndpointPath)] ?? null;
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
