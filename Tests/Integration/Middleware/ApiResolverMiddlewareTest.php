<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture\DummyController;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(ApiResolverMiddleware::class)]
final class ApiResolverMiddlewareTest extends AbstractMiddlewareTestCase
{
    private function makeMiddleware(
        ApiEndpointProvider $provider,
        EventDispatcherInterface $eventDispatcher,
        ?LoggerInterface $logger = null
    ): ApiResolverMiddleware {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        return GeneralUtility::makeInstance(
            ApiResolverMiddleware::class,
            $provider,
            $extensionConfiguration,
            $eventDispatcher,
            $logger ?? new NullLogger()
        );
    }

    private function makePassthroughDispatcher(): EventDispatcherInterface
    {
        return new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                return $event;
            }
        };
    }

    private function makeRequest(string $uri, string $method = 'GET'): ServerRequest
    {
        $site = $this->createStub(SiteInterface::class);
        $site->method('getBase')->willReturn(new Uri('https://example.com/lang/'));

        return (new ServerRequest(new Uri($uri), $method))
            ->withAttribute('site', $site);
    }

    #[Test]
    public function middleware_routs_api_request_to_endpoint(): void //phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher());
        $request = $this->makeRequest('http://example.com/lang/api/v1/my/api-endpoint');

        $response = $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals((new JsonResponse(['success' => true]))->getBody()->getContents(), $response->getBody()->getContents());
    }

    #[Test]
    public function middleware_routes_api_request_with_path_parameters_to_endpoint(): void //phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethodWithParams', 'GET', '/v1/my/api-endpoint/{param1}/{param2}');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher());
        $request = $this->makeRequest('http://example.com/lang/api/v1/my/api-endpoint/123/parValue');

        $response = $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(
            (new JsonResponse([
                'success' => true,
                'parameters' => [
                    'param1' => 123,
                    'param2' => 'parValue',
                    'requestUri' => 'http://example.com/lang/api/v1/my/api-endpoint/123/parValue'
                ]
            ]))->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }

    #[Test]
    public function middleware_logs_warning_when_api_path_has_no_matching_endpoint(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/other-endpoint');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('API endpoint not found'),
                $this->arrayHasKey('method')
            );

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher(), $logger);
        $request = $this->makeRequest('http://example.com/lang/api/v1/unknown-endpoint');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $middleware->process($request, $handler);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(['error' => 'Not Found'], json_decode($response->getBody()->getContents(), true));
    }

    #[Test]
    public function middleware_does_not_log_for_non_api_requests(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher(), $logger);
        $request = $this->makeRequest('http://example.com/lang/not-api/some-page');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new JsonResponse([]));

        $middleware->process($request, $handler);
    }

    #[Test]
    public function middleware_dispatches_modify_api_response_event(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $eventDispatcher = new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                if ($event instanceof ModifyApiResponseEvent) {
                    $response = $event->getResponse();
                    $response = $response->withHeader('X-Test-Header', 'test-value');
                    $event->setResponse($response);
                }

                return $event;
            }
        };

        $middleware = $this->makeMiddleware($apiEndpointProvider, $eventDispatcher);
        $request = $this->makeRequest('http://example.com/lang/api/v1/my/api-endpoint');

        $response = $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->hasHeader('X-Test-Header'), 'Response should have the custom header added by event listener');
        $this->assertEquals(['test-value'], $response->getHeader('X-Test-Header'));
    }
}
