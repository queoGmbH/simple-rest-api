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
    public function provides_endpoint_path_without_parameters(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint('MyApiController', 'myApiMethod', '/v1/my-endpoint/{someArg}/{otherArg}', 'GET', ['someArg', 'otherArg']);

        $this->assertSame('/v1/my-endpoint', $apiEndpoint->getPathWithoutParameters());
    }

    #[Test]
    public function provides_summary_and_description(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            'My Endpoint Summary',
            'This is a detailed description of my endpoint'
        );

        $this->assertSame('My Endpoint Summary', $apiEndpoint->summary);
        $this->assertSame('This is a detailed description of my endpoint', $apiEndpoint->description);
    }

    #[Test]
    public function allows_empty_summary_and_description(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            []
        );

        $this->assertSame('', $apiEndpoint->summary);
        $this->assertSame('', $apiEndpoint->description);
        $this->assertSame([], $apiEndpoint->parameters);
    }
}
