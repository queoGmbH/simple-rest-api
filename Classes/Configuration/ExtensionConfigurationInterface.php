<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

interface ExtensionConfigurationInterface
{
    public function getApiBasePath(): string;

    public function showInternalEndpoints(): bool;
}
