<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Provider;

use InvalidArgumentException;
use stdClass;
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

        $apiEndpointProvider->addEndpoint(stdClass::class, 'myEndpoint', 'GET', '/v1/my-api-endpoint');

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/v1/my-api-endpoint');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame(stdClass::class, $actualEndpoint->className);
        $this->assertSame('myEndpoint', $actualEndpoint->method);
        $this->assertSame('/v1/my-api-endpoint', $actualEndpoint->path);
        $this->assertSame('GET', $actualEndpoint->httpMethod);
        $this->assertTrue($actualEndpoint->parameters->isEmpty());
    }

    #[Test]
    public function finds_endpoint_with_parameters_from_api_request(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint(stdClass::class, 'myEndpoint', 'GET', '/v1/my-api-endpoint/{param1}/{param2}');

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::any())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::any())->method('getEndpointPath')->willReturn('/v1/my-api-endpoint/value1/value2');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame(stdClass::class, $actualEndpoint->className);
        $this->assertSame('myEndpoint', $actualEndpoint->method);
        $this->assertSame('/v1/my-api-endpoint/{param1}/{param2}', $actualEndpoint->path);
        $this->assertSame('GET', $actualEndpoint->httpMethod);
        // stdClass has no real method, so reflection fails but we still create parameter objects from path
        $this->assertCount(2, $actualEndpoint->parameters);
        $param1 = $actualEndpoint->parameters->getByIndex(0);
        $param2 = $actualEndpoint->parameters->getByIndex(1);
        $this->assertNotNull($param1);
        $this->assertNotNull($param2);
        $this->assertSame('param1', $param1->name);
        $this->assertSame('param2', $param2->name);
    }

    #[Test]
    public function adds_endpoint_with_summary_and_description(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint(
            stdClass::class,
            'myEndpoint',
            'GET',
            '/v1/my-api-endpoint',
            'My API Summary',
            'This is a detailed description of the API endpoint'
        );

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/v1/my-api-endpoint');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame('My API Summary', $actualEndpoint->summary);
        $this->assertSame('This is a detailed description of the API endpoint', $actualEndpoint->description);
    }

    #[Test]
    public function adds_versioned_endpoint_with_automatic_path_prefix(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        // Path WITHOUT version prefix, but WITH version parameter
        $apiEndpointProvider->addEndpoint(
            stdClass::class,
            'myEndpoint',
            'GET',
            '/users',  // No /v1/ prefix here
            '',
            '',
            [],
            '1'  // Version specified separately
        );

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/v1/users');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame('/v1/users', $actualEndpoint->path);
        $this->assertSame('1', $actualEndpoint->version);
    }

    #[Test]
    public function extracts_major_version_from_semantic_version(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint(
            stdClass::class,
            'myEndpoint',
            'GET',
            '/users',
            '',
            '',
            [],
            '1.2.3'  // Semantic version
        );

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        // Should use major version (1) in URL
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/v1/users');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame('/v1/users', $actualEndpoint->path);
        $this->assertSame('1.2.3', $actualEndpoint->version);  // Original version preserved
    }

    #[Test]
    public function allows_unversioned_endpoints(): void // phpcs:ignore
    {
        $apiEndpointProvider = new ApiEndpointProvider();

        $apiEndpointProvider->addEndpoint(
            stdClass::class,
            'myEndpoint',
            'GET',
            '/health',  // No version
            '',
            '',
            []  // No version parameter
        );

        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiRequest->expects(self::once())->method('getHttpMethod')->willReturn('GET');
        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/health');

        $actualEndpoint = $apiEndpointProvider->getEndpoint($apiRequest);

        $this->assertNotNull($actualEndpoint);
        $this->assertSame('/health', $actualEndpoint->path);
        $this->assertNull($actualEndpoint->version);
    }

    #[Test]
    public function throws_exception_when_path_contains_manual_version_prefix(): void // phpcs:ignore
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1733918400);
        $this->expectExceptionMessageMatches('/should not contain version prefix/');

        $apiEndpointProvider = new ApiEndpointProvider();

        // This should throw: path has /v1/ but version is also specified
        $apiEndpointProvider->addEndpoint(
            stdClass::class,
            'myEndpoint',
            'GET',
            '/v1/users',  // WRONG - manual version prefix
            '',
            '',
            [],
            '1'  // Version also specified
        );
    }
}
