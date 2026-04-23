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
use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture\DummyController;
use RuntimeException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
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
        return GeneralUtility::makeInstance(
            ApiResolverMiddleware::class,
            $provider,
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
    public function middleware_routes_api_request_to_endpoint(): void //phpcs:ignore
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

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
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

    #[Test]
    public function middleware_returns_400_when_parameter_cannot_be_coerced_to_declared_type(): void // phpcs:ignore
    {
        // Arrange
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        // requiresIntParam expects an int but we pass 'not-an-int'
        $apiEndpointProvider->addEndpoint(DummyController::class, 'requiresIntParam', 'GET', '/v1/typed/{id}');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher(), $logger);
        $request = $this->makeRequest('http://example.com/lang/api/v1/typed/not-an-int');

        // Act
        $response = $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        // Assert
        self::assertSame(400, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($body);
        self::assertArrayHasKey('error', $body);
    }

    #[Test]
    public function middleware_throws_runtime_exception_when_endpoint_returns_non_response(): void // phpcs:ignore
    {
        // Arrange
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'returnsNonResponse', 'GET', '/v1/bad-endpoint');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher());
        $request = $this->makeRequest('http://example.com/lang/api/v1/bad-endpoint');

        // Act + Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has to return a ResponseInterface');

        $middleware->process($request, $this->createStub(RequestHandlerInterface::class));
    }

    #[Test]
    public function middleware_dispatches_before_parameter_mapping_event(): void // phpcs:ignore
    {
        // Arrange
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/event-test');

        $beforeEventDispatched = false;
        $eventDispatcher = new class ($beforeEventDispatched) implements EventDispatcherInterface {
            public bool $beforeDispatched = false;

            public function __construct(bool &$flag)
            {
                $this->beforeDispatched = &$flag;
            }

            public function dispatch(object $event): object
            {
                if ($event instanceof BeforeParameterMappingEvent) {
                    $this->beforeDispatched = true;
                }

                return $event;
            }
        };

        $middleware = $this->makeMiddleware($apiEndpointProvider, $eventDispatcher);
        $request = $this->makeRequest('http://example.com/lang/api/v1/event-test');

        // Act
        $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        // Assert
        self::assertTrue($eventDispatcher->beforeDispatched);
    }

    private function makeRequestForSiteWithCustomBasePath(string $uri, string $method = 'GET'): ServerRequest
    {
        $site = new Site('rest-site', 10, [
            'base' => 'http://rest.test:8080/',
            'settings' => ['simple_rest_api' => ['basePath' => '/rest/']],
        ]);

        return (new ServerRequest(new Uri($uri), $method))
            ->withAttribute('site', $site);
    }

    #[Test]
    public function middleware_routes_request_when_site_has_custom_base_path(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher());
        $request = $this->makeRequestForSiteWithCustomBasePath('http://rest.test:8080/rest/v1/my/api-endpoint');

        $response = $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(['success' => true], json_decode($response->getBody()->getContents(), true));
    }

    #[Test]
    public function middleware_does_not_route_default_api_prefix_when_site_has_custom_base_path(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new JsonResponse([]));

        $middleware = $this->makeMiddleware($apiEndpointProvider, $this->makePassthroughDispatcher());
        // /api/ is the default but this site is configured with /rest/ — must not match
        $request = $this->makeRequestForSiteWithCustomBasePath('http://rest.test:8080/api/v1/my/api-endpoint');

        $middleware->process($request, $handler);
    }

    #[Test]
    public function middleware_dispatches_after_parameter_mapping_event(): void // phpcs:ignore
    {
        // Arrange
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/event-after-test');

        $afterEventDispatched = false;
        $eventDispatcher = new class ($afterEventDispatched) implements EventDispatcherInterface {
            public bool $afterDispatched = false;

            public function __construct(bool &$flag)
            {
                $this->afterDispatched = &$flag;
            }

            public function dispatch(object $event): object
            {
                if ($event instanceof AfterParameterMappingEvent) {
                    $this->afterDispatched = true;
                }

                return $event;
            }
        };

        $middleware = $this->makeMiddleware($apiEndpointProvider, $eventDispatcher);
        $request = $this->makeRequest('http://example.com/lang/api/v1/event-after-test');

        // Act
        $middleware->process($request, $this->createStub(RequestHandlerInterface::class));

        // Assert
        self::assertTrue($eventDispatcher->afterDispatched);
    }
}
