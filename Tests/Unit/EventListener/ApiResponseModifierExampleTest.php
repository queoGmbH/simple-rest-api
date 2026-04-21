<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\EventListener\ApiResponseModifierExample;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiResponseModifierExample::class)]
final class ApiResponseModifierExampleTest extends UnitTestCase
{
    private function makeEndpoint(string $httpMethod = 'GET'): ApiEndpoint
    {
        /** @phpstan-var class-string $className */
        $className = 'TestController';
        return new ApiEndpoint($className, 'testMethod', '/v1/test', $httpMethod, new ApiEndpointParameterCollection());
    }

    private function makeApiRequest(): ApiRequest
    {
        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        $serverRequest = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);
        $config = new ExtensionConfiguration($serverRequest);
        return new ApiRequest($serverRequest, $config);
    }

    #[Test]
    public function add_custom_headers_adds_cors_headers(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint(), $this->makeApiRequest());

        // Act
        $listener->addCustomHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Access-Control-Allow-Origin'));
        self::assertSame('*', $modifiedResponse->getHeader('Access-Control-Allow-Origin')[0]);
    }

    #[Test]
    public function add_custom_headers_adds_allowed_methods_header(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint(), $this->makeApiRequest());

        // Act
        $listener->addCustomHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Access-Control-Allow-Methods'));
    }

    #[Test]
    public function add_custom_headers_adds_api_version_header(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint(), $this->makeApiRequest());

        // Act
        $listener->addCustomHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('X-API-Version'));
        self::assertSame('1.0', $modifiedResponse->getHeader('X-API-Version')[0]);
    }

    #[Test]
    public function add_custom_headers_adds_request_id_header(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint(), $this->makeApiRequest());

        // Act
        $listener->addCustomHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('X-Request-ID'));
        self::assertStringStartsWith('req_', $modifiedResponse->getHeader('X-Request-ID')[0]);
    }

    #[Test]
    public function add_caching_headers_sets_public_cache_for_get_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('GET'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Cache-Control'));
        $cacheControl = $modifiedResponse->getHeader('Cache-Control')[0];
        self::assertStringContainsString('public', $cacheControl);
    }

    #[Test]
    public function add_caching_headers_sets_expires_header_for_get_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('GET'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Expires'));
    }

    #[Test]
    public function add_caching_headers_sets_no_cache_for_post_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('POST'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Cache-Control'));
        $cacheControl = $modifiedResponse->getHeader('Cache-Control')[0];
        self::assertStringContainsString('no-cache', $cacheControl);
    }

    #[Test]
    public function add_caching_headers_sets_pragma_no_cache_for_post_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('POST'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        self::assertTrue($modifiedResponse->hasHeader('Pragma'));
        self::assertSame('no-cache', $modifiedResponse->getHeader('Pragma')[0]);
    }

    #[Test]
    public function add_caching_headers_sets_no_cache_for_put_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('PUT'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        $cacheControl = $modifiedResponse->getHeader('Cache-Control')[0];
        self::assertStringContainsString('no-cache', $cacheControl);
    }

    #[Test]
    public function add_caching_headers_sets_no_cache_for_delete_requests(): void // phpcs:ignore
    {
        // Arrange
        $listener = new ApiResponseModifierExample();
        $response = new JsonResponse(['data' => 'test']);
        $event = new ModifyApiResponseEvent($response, $this->makeEndpoint('DELETE'), $this->makeApiRequest());

        // Act
        $listener->addCachingHeaders($event);

        // Assert
        $modifiedResponse = $event->getResponse();
        $cacheControl = $modifiedResponse->getHeader('Cache-Control')[0];
        self::assertStringContainsString('no-cache', $cacheControl);
    }
}
