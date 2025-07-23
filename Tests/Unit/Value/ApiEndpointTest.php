<?php

declare(strict_types=1);

namespace Unit\Value;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpoint::class)]
final class ApiEndpointTest extends UnitTestCase
{
    #[Test]
    public function arguments_are_fetched_from_path(): void
    {
        $apiEndpoint = new ApiEndpoint('MyApiController', 'myApiMethod', '/api/v1/my-endpoint/{someArg}/{otherArg}', 'GET');

        $this->assertSame(
            ['someArg', 'otherArg'],
            $apiEndpoint->getArgumentNames()
        );
    }
}
