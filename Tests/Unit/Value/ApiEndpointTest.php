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
    public function provides_endpoint_path_without_parameters(): void
    {
        $apiEndpoint = new ApiEndpoint('MyApiController', 'myApiMethod', '/v1/my-endpoint/{someArg}/{otherArg}', 'GET', ['someArg', 'otherArg']);

        $this->assertSame('/v1/my-endpoint', $apiEndpoint->getPathWithoutParameters());
    }
}
