<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ModifyApiResponseEvent::class)]
final class ModifyApiResponseEventTest extends UnitTestCase
{
    #[Test]
    public function provides_response(): void // phpcs:ignore
    {
        $response = new JsonResponse(['data' => 'test']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($response, $endpoint, $apiRequest);

        $this->assertSame($response, $event->getResponse());
    }

    #[Test]
    public function provides_endpoint(): void // phpcs:ignore
    {
        $response = new JsonResponse(['data' => 'test']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($response, $endpoint, $apiRequest);

        $this->assertSame($endpoint, $event->getEndpoint());
    }

    #[Test]
    public function provides_api_request(): void // phpcs:ignore
    {
        $response = new JsonResponse(['data' => 'test']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($response, $endpoint, $apiRequest);

        $this->assertSame($apiRequest, $event->getApiRequest());
    }

    #[Test]
    public function allows_response_modification(): void // phpcs:ignore
    {
        $originalResponse = new JsonResponse(['data' => 'original']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($originalResponse, $endpoint, $apiRequest);

        // Modify the response by adding a header
        $modifiedResponse = $originalResponse->withHeader('X-Custom-Header', 'CustomValue');
        $event->setResponse($modifiedResponse);

        $this->assertNotSame($originalResponse, $event->getResponse());
        $this->assertSame($modifiedResponse, $event->getResponse());
        $this->assertTrue($event->getResponse()->hasHeader('X-Custom-Header'));
        $this->assertEquals(['CustomValue'], $event->getResponse()->getHeader('X-Custom-Header'));
    }

    #[Test]
    public function allows_complete_response_replacement(): void // phpcs:ignore
    {
        $originalResponse = new JsonResponse(['data' => 'original']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($originalResponse, $endpoint, $apiRequest);

        // Replace with completely new response
        $newResponse = new JsonResponse(['data' => 'replaced'], 201);
        $event->setResponse($newResponse);

        $this->assertSame($newResponse, $event->getResponse());
        $this->assertEquals(201, $event->getResponse()->getStatusCode());
        $this->assertEquals(
            '{"data":"replaced"}',
            $event->getResponse()->getBody()->getContents()
        );
    }

    #[Test]
    public function allows_adding_multiple_headers(): void // phpcs:ignore
    {
        $response = new JsonResponse(['data' => 'test']);
        $endpoint = new ApiEndpoint('TestClass', 'testMethod', '/v1/test', 'GET', []);
        $apiRequest = $this->createMock(ApiRequestInterface::class);

        $event = new ModifyApiResponseEvent($response, $endpoint, $apiRequest);

        // Add multiple headers
        $modifiedResponse = $response
            ->withHeader('X-API-Version', '1.0')
            ->withHeader('X-Request-ID', 'test-123')
            ->withHeader('Cache-Control', 'no-cache');

        $event->setResponse($modifiedResponse);

        $this->assertTrue($event->getResponse()->hasHeader('X-API-Version'));
        $this->assertTrue($event->getResponse()->hasHeader('X-Request-ID'));
        $this->assertTrue($event->getResponse()->hasHeader('Cache-Control'));
        $this->assertEquals(['1.0'], $event->getResponse()->getHeader('X-API-Version'));
        $this->assertEquals(['test-123'], $event->getResponse()->getHeader('X-Request-ID'));
        $this->assertEquals(['no-cache'], $event->getResponse()->getHeader('Cache-Control'));
    }
}
