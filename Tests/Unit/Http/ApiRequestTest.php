<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Queo\SimpleRestApi\Http\ApiRequest;

#[CoversClass(ApiRequest::class)]
final class ApiRequestTest extends UnitTestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function request_detects_current_incoming_request_is_an_api_request(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())->method('getAttribute')->with('site')->willReturn($site);
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $this->assertTrue($apiRequest->isApiRequest());
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function request_detects_current_incoming_request_as_non_api_request(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())->method('getAttribute')->with('site')->willReturn($site);
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/some/other/path'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $this->assertFalse($apiRequest->isApiRequest());
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function request_know_http_method(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())->method('getAttribute')->with('site')->willReturn($site);
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $currentRequest->expects(self::once())->method('getMethod')->willReturn('GET');

        $this->assertSame('GET', $apiRequest->getHttpMethod());
    }

    #[Test]
    public function request_can_provide_api_endpoint_path_for_comparison(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())->method('getAttribute')->with('site')->willReturn($site);
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $this->assertSame('/v1/my/example/endpoint', $apiRequest->getEndpointPath());
    }

    #[Test]
    public function request_can_provide_parameters_for_endpoint(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())->method('getAttribute')->with('site')->willReturn($site);
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint/123/value'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $apiEndpoint = new ApiEndpoint('MyClass', 'myEndpoint', '/v1/my/example/endpoint/{param1}/{param2}', 'GET', ['param1', 'param2']);

        $expectedParameters = new Parameters(
            [
                0 => 'param1',
                1 => 'param2'
            ],
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $this->assertEquals($expectedParameters, $apiRequest->getParameters($apiEndpoint));
    }
}
