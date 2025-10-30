<?php

declare(strict_types=1);

namespace Unit\Value;

use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \Queo\SimpleRestApi\Value\ApiEndpoint
 */
final class ApiEndpointTest extends UnitTestCase
{
    /**
     * @test
     */
    public function provides_endpoint_path_without_parameters(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint('MyApiController', 'myApiMethod', '/v1/my-endpoint/{someArg}/{otherArg}', 'GET', ['someArg', 'otherArg']);

        $this->assertSame('/v1/my-endpoint', $apiEndpoint->getPathWithoutParameters());
    }
}
