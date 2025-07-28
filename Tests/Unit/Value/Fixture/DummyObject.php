<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Value\Fixture;

use Psr\Http\Message\ServerRequestInterface;

final class DummyObject
{
    public function integerParamMethod(int $param): void
    {
    }

    public function floatParamMethod(float $param): void
    {
    }

    public function boolParamMethod(bool $param): void
    {
    }

    public function stringParamMethod(string $param): void
    {
    }

    public function requestParamMethod(ServerRequestInterface $param): void
    {
    }

    public function objectParamMethod(DummyObject $param)
    {
    }
}
