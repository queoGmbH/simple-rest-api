<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

final readonly class ApiEndpoint implements ApiEndpointInterface
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

    public function getPathWithoutParameters(): string
    {
        $pathParts = explode('/', trim($this->path, '/'));

        $identifierPathParts = [];

        foreach ($pathParts as $pathPart) {
            if (!preg_match('/{([0-9a-zA-Z]+)}/', $pathPart)) {
                $identifierPathParts[] = $pathPart;
            }
        }

        return '/' . implode('/', $identifierPathParts);
    }
}
