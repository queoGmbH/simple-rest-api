<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class AsApiEndpoint
{
    public const TAG_NAME = 'api.endpoint';

    public function __construct(
        public string $method,
        public string $path
    ) {
    }
}
