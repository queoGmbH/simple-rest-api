<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;

final class Parameters
{
    public function __construct(
        private readonly array $endpointParameters,
        private readonly array $parameterValues,
        private readonly ServerRequestInterface $request
    )
    {
    }
}
