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
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $summary = '',
        public string $description = '',
        public array $tags = []
    ) {
    }
}
