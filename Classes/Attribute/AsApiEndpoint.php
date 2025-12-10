<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class AsApiEndpoint
{
    public const TAG_NAME = 'api.endpoint';

    /**
     * @param array<string> $tags
     * @param array<mixed> $requestBody OpenAPI request body specification
     * @param array<mixed> $responses OpenAPI responses specification
     * @param array<mixed> $parameters OpenAPI parameters specification
     * @param array<mixed> $security OpenAPI security requirements
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $summary = '',
        public string $description = '',
        public array $tags = [],
        public array $requestBody = [],
        public array $responses = [],
        public array $parameters = [],
        public array $security = []
    ) {
    }
}
