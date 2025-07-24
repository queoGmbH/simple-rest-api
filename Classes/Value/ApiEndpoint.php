<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

final readonly class ApiEndpoint
{
    /**
     * @param array<string> $parameterList
     */
    public function __construct(
        public string $className,
        public string $method,
        public string $path,
        public string $httpMethod,
        public array $parameterList
    ) {
    }

    public function parameterCount(): int
    {
        return count($this->parameterList);
    }
}
