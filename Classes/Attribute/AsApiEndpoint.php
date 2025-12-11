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
     * @param string|null $version API version (e.g., '1', '1.0', '1.2.3'). If specified, the path will be automatically prefixed with /v{major}/
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $summary = '',
        public string $description = '',
        public array $tags = [],
        public ?string $version = null
    ) {
    }
}
