<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

use Psr\Http\Message\UriInterface;

final readonly class ApiPath
{
    private string $compareBasePath;

    public function __construct(private UriInterface $siteBase, private UriInterface $requestUri, private string $apiBasePath)
    {
        $this->compareBasePath = rtrim($this->siteBase->getPath(), '/') . '/' . ltrim($this->apiBasePath, '/');
    }

    public function isApiPath(): bool
    {
        return str_starts_with($this->requestUri->getPath(), $this->compareBasePath);
    }

    public function getEndpointPath(): string
    {
        $endpointPath = ltrim($this->requestUri->getPath(), $this->siteBase->getPath());

        if (!str_starts_with($endpointPath, '/')) {
            return '/' . $endpointPath;
        }

        return $endpointPath;
    }

    /**
     * @return array<string>
     */
    public function getParameterValuesFromPath(int $parameterCount): array
    {
        $endpointPathArray = explode('/', trim($this->getEndpointPath(), '/'));

        return array_splice($endpointPathArray, -$parameterCount);
    }
}
