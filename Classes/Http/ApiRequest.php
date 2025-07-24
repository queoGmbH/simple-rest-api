<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final class ApiRequest implements ApiRequestInterface
{
    private SiteInterface $site;
    private UriInterface $requestUri;
    private UriInterface $baseUri;
    private string $apiBasePath;
    private string $compareBasePath;

    public function __construct(private readonly ServerRequestInterface $request, private readonly ExtensionConfigurationInterface $extensionConfiguration)
    {
        $this->site = $this->request->getAttribute('site');
        $this->requestUri = $this->request->getUri();
        $this->baseUri = $this->site->getBase();
        $this->apiBasePath = $this->extensionConfiguration->getApiBasePath();
        $this->compareBasePath = rtrim($this->baseUri->getPath(), '/') . '/' . ltrim($this->apiBasePath, '/');
    }

    public function isApiRequest(): bool
    {
        return str_starts_with($this->requestUri->getPath(), $this->compareBasePath);
    }

    public function getHttpMethod(): string
    {
        return $this->request->getMethod();
    }

    public function getEndpointPath(): string
    {
        $incomePath = $this->requestUri->getPath();
        $basePath = '/' . trim($this->baseUri->getPath(), '/');
        $apiBasePath = '/' . trim($this->apiBasePath, '/');

        $comparePath = '';

        if (str_starts_with($incomePath, $basePath)) {
            $comparePath = substr($incomePath, strlen($basePath));
        }

        if (str_starts_with($comparePath, $apiBasePath)) {
            $comparePath = substr($comparePath, strlen($apiBasePath));
        }

        return $comparePath;
    }

    public function getParameters(ApiEndpoint $apiEndpoint): Parameters
    {
        $parameterList = $apiEndpoint->parameterList;
        $endpointPath = $this->getEndpointPath();
        $parameterPathPart = trim(str_replace($apiEndpoint->getPathWithoutParameters(), '', $endpointPath), '/');
        $parameterPathPartArray = explode('/', $parameterPathPart);

        return new Parameters($parameterList, $parameterPathPartArray, $this->request);
    }
}
