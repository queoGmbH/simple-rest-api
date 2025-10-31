<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture\DummyController;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiResolverMiddleware::class)]
final class ApiResolverMiddlewareTest extends UnitTestCase
{
    #[Test]
    public function middleware_routs_api_request_to_endpoint(): void //phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $eventDispatcher = new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                return $event;
            }
        };

        $middleware = GeneralUtility::makeInstance(ApiResolverMiddleware::class, $apiEndpointProvider, $extensionConfiguration, $eventDispatcher);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/lang/api/v1/my/api-endpoint';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $site = $this->createMock(SiteInterface::class);
        $site->expects(self::any())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('site', $site);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals((new JsonResponse(['success' => true]))->getBody()->getContents(), $response->getBody()->getContents());
    }

    #[Test]
    public function middleware_routes_api_request_with_path_parameters_to_endpoint(): void //phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethodWithParams', 'GET', '/v1/my/api-endpoint/{param1}/{param2}');

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $eventDispatcher = new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                return $event;
            }
        };

        $middleware = GeneralUtility::makeInstance(ApiResolverMiddleware::class, $apiEndpointProvider, $extensionConfiguration, $eventDispatcher);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/lang/api/v1/my/api-endpoint/123/parValue';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $site = $this->createMock(SiteInterface::class);
        $site->expects(self::any())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('site', $site);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

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
    public function middleware_dispatches_modify_api_response_event(): void // phpcs:ignore
    {
        $apiEndpointProvider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $apiEndpointProvider->addEndpoint(DummyController::class, 'dummyApiMethod', 'GET', '/v1/my/api-endpoint');

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        $eventDispatcher = new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                if ($event instanceof ModifyApiResponseEvent) {
                    // Test that we can modify the response
                    $response = $event->getResponse();
                    $response = $response->withHeader('X-Test-Header', 'test-value');
                    $event->setResponse($response);
                }

                return $event;
            }
        };

        $middleware = GeneralUtility::makeInstance(ApiResolverMiddleware::class, $apiEndpointProvider, $extensionConfiguration, $eventDispatcher);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/lang/api/v1/my/api-endpoint';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $site = $this->createMock(SiteInterface::class);
        $site->expects(self::any())->method('getBase')->willReturn(new Uri('https://example.com/lang/'));
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('site', $site);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue($response->hasHeader('X-Test-Header'), 'Response should have the custom header added by event listener');
        $this->assertEquals(['test-value'], $response->getHeader('X-Test-Header'));
    }
}
