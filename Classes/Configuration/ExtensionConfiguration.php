<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

final class ExtensionConfiguration
{
    public function getApiBasePath(): string
    {
        return '/api/';
    }
}
