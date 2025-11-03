<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Http;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Value\ApiEndpoint;

interface ApiRequestInterface
{
    public function isApiRequest(): bool;

    public function getHttpMethod(): string;

    public function getEndpointPath(): string;

    public function getParameters(ApiEndpoint $apiEndpoint): EndpointParameterResolver;

    public function getRequest(): ServerRequestInterface;
}
