<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointProvider::class)]
final class ApiEndpointProviderTest extends UnitTestCase
{
    #[Test]
    public function finds_endpoint_from_api_request(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint('MyClass', 'myEndpoint', 'GET', '/v1/my-api-endpoint');

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/v1/my-api-endpoint');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $expectedEndpoint = new ApiEndpoint(
            'MyClass',
            'myEndpoint',
            '/v1/my-api-endpoint',
            'GET',
            []
        );

        $this->assertEquals($expectedEndpoint, $actualEndpoint);
    }

    #[Test]
    public function finds_endpoint_with_parameters_from_api_request(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint('MyClass', 'myEndpoint', 'GET', '/v1/my-api-endpoint/{param1}/{param2}');

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::any())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::any())->method('getEndpointPath')->willReturn('/v1/my-api-endpoint/value1/value2');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $expectedEndpoint = new ApiEndpoint(
            'MyClass',
            'myEndpoint',
            '/v1/my-api-endpoint/{param1}/{param2}',
            'GET',
            [
                0 => 'param1',
                1 => 'param2'
            ]
        );

        $this->assertEquals($expectedEndpoint, $actualEndpoint);
    }
}
