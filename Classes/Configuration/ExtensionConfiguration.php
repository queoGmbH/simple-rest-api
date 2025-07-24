<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

// @todo: Introduce intetrface
final class ExtensionConfiguration implements ExtensionConfigurationInterface
{
    public function getApiBasePath(): string
    {
        // @todo: Fetch configuration from site set
        return '/api/';
    }
}
