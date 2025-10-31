<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

final readonly class ApiEndpointParameter
{
    public function __construct(
        public string $name,
        public string $type = '',
        public string $description = ''
    ) {
    }
}
