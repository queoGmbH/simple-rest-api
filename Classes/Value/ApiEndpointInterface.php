<?php
declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

interface ApiEndpointInterface
{

    public function parameterCount(): int;

    public function getPathWithoutParameters(): string;
}
