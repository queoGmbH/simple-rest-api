<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture\DummyController;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Comprehensive integration tests for all HTTP methods
 *
 * @class-string
 */
#[CoversClass(ApiResolverMiddleware::class)]
final class HttpMethodsIntegrationTest extends UnitTestCase
{
    private function createMiddleware(ApiEndpointProvider $provider): ApiResolverMiddleware
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $eventDispatcher = new class implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                return $event;
            }
        };

        return GeneralUtility::makeInstance(
            ApiResolverMiddleware::class,
            $provider,
            $extensionConfiguration,
            $eventDispatcher
        );
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     * @param array<string, string> $queryParams
     */
    private function createRequest(
        string $method,
        string $path,
        array $body = [],
        array $headers = [],
        array $queryParams = []
    ): ServerRequest {
        // Set up $_SERVER globals like existing tests do
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = '/lang' . $path;
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        if ($queryParams !== []) {
            $_SERVER['QUERY_STRING'] = http_build_query($queryParams);
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }

        // Set headers in $_SERVER
        foreach ($headers as $name => $value) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            $_SERVER[$serverKey] = $value;
        }

        $request = ServerRequestFactory::fromGlobals();

        // Add body if provided
        if ($body !== []) {
            $jsonBody = json_encode($body);
            assert($jsonBody !== false);
            $stream = new Stream('php://memory', 'rw');
            $stream->write($jsonBody);
            $stream->rewind();
            $request = $request->withBody($stream);

            if (!isset($headers['Content-Type'])) {
                $request = $request->withHeader('Content-Type', 'application/json');
            }
        }

        if ($queryParams !== []) {
            $request = $request->withQueryParams($queryParams);
        }

        $site = $this->createMock(SiteInterface::class);
        $site->method('getBase')->willReturn(new Uri('https://example.com/lang/'));

        return $request->withAttribute('site', $site);
    }

    #[Test]
    public function postMethodCreatesResource(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'createResource', 'POST', '/v1/resources');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'POST',
            '/api/v1/resources',
            ['name' => 'Test Resource', 'description' => 'Test Description']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('POST', $body['method']);
        assert(is_array($body['data']));
        $this->assertEquals('Test Resource', $body['data']['name']);
        $this->assertTrue($body['created']);
    }

    #[Test]
    public function postMethodWithPathParameter(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'createSubResource', 'POST', '/v1/resources/{resourceId}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'POST',
            '/api/v1/resources/42',
            ['title' => 'Sub Item']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('POST', $body['method']);
        $this->assertEquals(42, $body['resourceId']);
        assert(is_array($body['data']));
        $this->assertEquals('Sub Item', $body['data']['title']);
    }

    #[Test]
    public function putMethodUpdatesResource(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'updateResource', 'PUT', '/v1/resources/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'PUT',
            '/api/v1/resources/123',
            ['name' => 'Updated Resource', 'status' => 'active']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('PUT', $body['method']);
        $this->assertEquals(123, $body['id']);
        assert(is_array($body['data']));
        $this->assertEquals('Updated Resource', $body['data']['name']);
        $this->assertTrue($body['updated']);
    }

    #[Test]
    public function patchMethodPartiallyUpdatesResource(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'patchResource', 'PATCH', '/v1/resources/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'PATCH',
            '/api/v1/resources/456',
            ['status' => 'completed']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('PATCH', $body['method']);
        $this->assertEquals(456, $body['id']);
        assert(is_array($body['data']));
        $this->assertEquals('completed', $body['data']['status']);
        $this->assertTrue($body['patched']);
    }

    #[Test]
    public function deleteMethodRemovesResource(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'deleteResource', 'DELETE', '/v1/resources/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest('DELETE', '/api/v1/resources/789');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('DELETE', $body['method']);
        $this->assertEquals(789, $body['id']);
        $this->assertTrue($body['deleted']);
    }

    #[Test]
    public function deleteMethodWithConfirmationInBody(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'deleteResourceWithConfirmation', 'DELETE', '/v1/confirm/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'DELETE',
            '/api/v1/confirm/999',
            ['confirmed' => true]
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('DELETE', $body['method']);
        $this->assertEquals(999, $body['id']);
        $this->assertTrue($body['deleted']);
        $this->assertTrue($body['confirmed']);
    }

    #[Test]
    public function deleteMethodReturnsErrorWithoutConfirmation(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'deleteResourceWithConfirmation', 'DELETE', '/v1/confirm-delete/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'DELETE',
            '/api/v1/confirm-delete/999',
            ['confirmed' => false]
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('Deletion requires confirmation', $body['error']);
    }

    #[Test]
    public function handlesMultipleParameterTypes(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(
            DummyController::class,
            'multiTypeParams',
            'GET',
            '/v1/types/{intParam}/{stringParam}/{floatParam}/{boolParam}'
        );

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'GET',
            '/api/v1/types/42/hello/3.14/1'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertIsInt($body['intParam']);
        $this->assertEquals(42, $body['intParam']);
        $this->assertIsString($body['stringParam']);
        $this->assertEquals('hello', $body['stringParam']);
        $this->assertIsFloat($body['floatParam']);
        $this->assertEquals(3.14, $body['floatParam']);
        $this->assertIsBool($body['boolParam']);
        $this->assertTrue($body['boolParam']);
    }

    #[Test]
    public function handlesRequestHeaders(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'checkHeaders', 'GET', '/v1/headers');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'GET',
            '/api/v1/headers',
            [],
            [
                'Authorization' => 'Bearer token123',
                'Content-Type' => 'application/json',
                'X-Custom-Header' => 'custom-value'
            ]
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertTrue($body['hasAuthHeader']);
        $this->assertEquals('Bearer token123', $body['authHeader']);
        $this->assertEquals('application/json', $body['contentType']);
        $this->assertEquals('custom-value', $body['customHeader']);
    }

    #[Test]
    public function handlesQueryParameters(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'checkQueryParams', 'GET', '/v1/search');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'GET',
            '/api/v1/search',
            [],
            [],
            ['limit' => '10', 'offset' => '20', 'q' => 'test query']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertTrue($body['hasLimit']);
        $this->assertTrue($body['hasOffset']);
        assert(is_array($body['queryParams']));
        $this->assertEquals('10', $body['queryParams']['limit']);
        $this->assertEquals('20', $body['queryParams']['offset']);
        $this->assertEquals('test query', $body['queryParams']['q']);
    }

    #[Test]
    public function handlesNotFoundStatusCode(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'notFoundResource', 'GET', '/v1/resources/{id}');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest('GET', '/api/v1/resources/999');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertEquals('Resource not found', $body['error']);
        $this->assertEquals(999, $body['id']);
    }

    #[Test]
    public function handlesValidationErrors(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'validateInput', 'POST', '/v1/validate');

        $middleware = $this->createMiddleware($provider);

        // Test with missing required fields
        $request = $this->createRequest(
            'POST',
            '/api/v1/validate',
            ['name' => ''] // missing email
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
        $this->assertContains('Name is required', $body['errors']);
        $this->assertContains('Email is required', $body['errors']);
    }

    #[Test]
    public function handlesValidInput(): void
    {
        $provider = GeneralUtility::makeInstance(ApiEndpointProvider::class);
        $provider->addEndpoint(DummyController::class, 'validateInput', 'POST', '/v1/validate');

        $middleware = $this->createMiddleware($provider);

        $request = $this->createRequest(
            'POST',
            '/api/v1/validate',
            ['name' => 'John Doe', 'email' => 'john@example.com']
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        assert(is_array($body));
        $this->assertTrue($body['success']);
        assert(is_array($body['data']));
        $this->assertEquals('John Doe', $body['data']['name']);
        $this->assertEquals('john@example.com', $body['data']['email']);
    }
}
