<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Http;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Value\ApiEndpoint;

interface ApiRequestInterface
{
    public function isApiRequest(): bool;

    public function getHttpMethod(): string;

    public function getEndpointPath(): string;

    public function getParameters(ApiEndpoint $apiEndpoint): Parameters;

    public function getRequest(): ServerRequestInterface;
}
