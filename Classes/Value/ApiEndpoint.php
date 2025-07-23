<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

final class ApiEndpoint
{
    public function __construct(
        public readonly string $className,
        public readonly string $method,
        public readonly string $path,
        public readonly string $httpMethod,
        public readonly array $parameterList
    )
    {
    }

    public function parameterCount(): int
    {
        return count($this->parameterList);
    }
}
