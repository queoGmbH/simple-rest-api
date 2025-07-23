<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AsApiEndpoint
{
    const TAG_NAME = 'api.endpoint';

    public function __construct(
        public readonly string $method,
        public readonly string $path
    ) {
    }
}
