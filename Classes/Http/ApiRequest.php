<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Http;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final readonly class ApiRequest implements ApiRequestInterface
{
    private SiteInterface $site;

    private UriInterface $requestUri;

    private UriInterface $baseUri;

    private string $apiBasePath;

    private string $compareBasePath;

    public function __construct(private ServerRequestInterface $request, private ExtensionConfigurationInterface $extensionConfiguration)
    {
        $site = $this->request->getAttribute('site');

        if (!$site instanceof SiteInterface) {
            throw new RuntimeException('No site provided!', 1072552822);
        }

        $this->site = $site;
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
            return substr($comparePath, strlen($apiBasePath));
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

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
