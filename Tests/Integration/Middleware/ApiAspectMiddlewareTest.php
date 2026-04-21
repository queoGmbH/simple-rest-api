<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use Queo\SimpleRestApi\Middleware\ApiAspectMiddleware;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(ApiAspectMiddleware::class)]
final class ApiAspectMiddlewareTest extends AbstractMiddlewareTestCase
{
    #[Test]
    public function sets_simple_rest_api_aspect_on_context_and_delegates_to_handler(): void // phpcs:ignore
    {
        // Arrange
        $context = GeneralUtility::makeInstance(Context::class);
        $middleware = GeneralUtility::makeInstance(ApiAspectMiddleware::class, $context);

        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);

        // Assert — aspect is registered on the context
        self::assertTrue($context->hasAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER));
        $aspect = $context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);
        self::assertInstanceOf(SimpleRestApiAspect::class, $aspect);
    }

    #[Test]
    public function returns_response_from_handler(): void // phpcs:ignore
    {
        // Arrange
        $context = GeneralUtility::makeInstance(Context::class);
        $middleware = GeneralUtility::makeInstance(ApiAspectMiddleware::class, $context);

        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);

        $expectedResponse = new JsonResponse(['success' => true], 200);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($expectedResponse);

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        self::assertSame($expectedResponse, $response);
    }

    #[Test]
    public function aspect_provides_request_via_getter(): void // phpcs:ignore
    {
        // Arrange
        $context = GeneralUtility::makeInstance(Context::class);
        $middleware = GeneralUtility::makeInstance(ApiAspectMiddleware::class, $context);

        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);

        // Assert — the aspect was created with the correct request
        /** @var SimpleRestApiAspect $aspect */
        $aspect = $context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);
        self::assertSame($request, $aspect->getRequest());
    }
}
