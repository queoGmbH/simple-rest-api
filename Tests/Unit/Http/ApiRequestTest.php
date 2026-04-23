<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Http;

use RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
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

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
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

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
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

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $this->assertSame('/v1/my/example/endpoint', $apiRequest->getEndpointPath());
    }

    #[Test]
    public function request_can_provide_api_endpoint_path_when_base_path_is_root(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/api/v1/my/example/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/'));
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

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/lang/api/v1/my/example/endpoint/123/value'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $apiEndpoint = new ApiEndpoint(
            'MyClass', // @phpstan-ignore-line argument.type
            'myEndpoint',
            '/v1/my/example/endpoint/{param1}/{param2}',
            'GET',
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1'),
                new ApiEndpointParameter('param2')
            )
        );

        $expectedParameters = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1'),
                new ApiEndpointParameter('param2')
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $this->assertEquals($expectedParameters, $apiRequest->getParameters($apiEndpoint));
    }

    #[Test]
    public function request_uses_language_specific_base_uri_when_site_language_is_available(): void // phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $siteLanguage = $this->createMock(SiteLanguage::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        // Mock getAttribute to return site and siteLanguage
        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => $siteLanguage,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/de/api/v1/my/example/endpoint'));

        // SiteLanguage should return the language-specific base
        $siteLanguage->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/de/'));

        // Site's getBase should NOT be called when language is available
        $site->expects(self::never())->method('getBase');

        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        $this->assertTrue($apiRequest->isApiRequest());
        $this->assertSame('/v1/my/example/endpoint', $apiRequest->getEndpointPath());
    }

    #[Test]
    public function provides_underlying_server_request(): void // phpcs:ignore
    {
        // Arrange
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/api/v1/endpoint'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        // Act + Assert
        $this->assertSame($currentRequest, $apiRequest->getRequest());
    }

    #[Test]
    public function throws_runtime_exception_when_no_site_attribute_on_request(): void // phpcs:ignore
    {
        // Arrange
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn(null);

        // Act + Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No site provided!');

        new ApiRequest($currentRequest, $extensionConfiguration);
    }

    #[Test]
    public function detects_api_request_on_subdirectory_site(): void // phpcs:ignore
    {
        // Arrange — TYPO3's Site constructor combines site base '/subdir/' with language
        // base '/' and stores the result as the SiteLanguage base URI. Simulate that here
        // by having the language mock return the already-combined 'http://localhost:8080/subdir/'.
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $siteLanguage = $this->createMock(SiteLanguage::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => $siteLanguage,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')
            ->willReturn(new Uri('http://localhost:8080/subdir/api/v1/my/endpoint'));
        $siteLanguage->expects(self::once())->method('getBase')
            ->willReturn(new Uri('http://localhost:8080/subdir/'));
        $site->expects(self::never())->method('getBase');
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        // Act
        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        // Assert
        self::assertTrue($apiRequest->isApiRequest());
    }

    #[Test]
    public function returns_correct_endpoint_path_for_subdirectory_site(): void // phpcs:ignore
    {
        // Arrange — same combined-URI assumption as the previous test; verifies that
        // getEndpointPath() strips both the site prefix '/subdir/' and the API prefix '/api/'.
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $siteLanguage = $this->createMock(SiteLanguage::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => $siteLanguage,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')
            ->willReturn(new Uri('http://localhost:8080/subdir/api/v1/my/endpoint'));
        $siteLanguage->expects(self::once())->method('getBase')
            ->willReturn(new Uri('http://localhost:8080/subdir/'));
        $site->expects(self::never())->method('getBase');
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        // Act
        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        // Assert
        self::assertSame('/v1/my/endpoint', $apiRequest->getEndpointPath());
    }

    #[Test]
    public function detects_non_api_request_on_subdirectory_site(): void // phpcs:ignore
    {
        // Arrange — path '/subdir/some-page' does not start with '/subdir/api/'
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $siteLanguage = $this->createMock(SiteLanguage::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => $siteLanguage,
                default => null
            });
        $currentRequest->expects(self::once())->method('getUri')
            ->willReturn(new Uri('http://localhost:8080/subdir/some-page'));
        $siteLanguage->expects(self::once())->method('getBase')
            ->willReturn(new Uri('http://localhost:8080/subdir/'));
        $site->expects(self::never())->method('getBase');
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        // Act
        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        // Assert
        self::assertFalse($apiRequest->isApiRequest());
    }

    #[Test]
    public function returns_empty_path_when_incoming_path_does_not_start_with_site_base_or_api_base(): void // phpcs:ignore
    {
        // Arrange — request URI does not start with the site base path
        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $extensionConfiguration = $this->createMock(ExtensionConfigurationInterface::class);

        $currentRequest->expects(self::exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(fn($key): ?MockObject => match ($key) {
                'site' => $site,
                'language' => null,
                default => null
            });
        // URI path '/other/path' does not start with site base '/lang/'
        $currentRequest->expects(self::once())->method('getUri')->willReturn(new Uri('https://example.com/other/path'));
        $site->expects(self::once())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $extensionConfiguration->expects(self::once())->method('getApiBasePath')->willReturn('/api/');

        $apiRequest = new ApiRequest($currentRequest, $extensionConfiguration);

        // Act
        $endpointPath = $apiRequest->getEndpointPath();

        // Assert — returns empty string when path does not match any known base
        $this->assertSame('', $endpointPath);
    }
}
