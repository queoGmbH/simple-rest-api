<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Middleware\CacheHashFixer;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(CacheHashFixer::class)]
final class CacheHashFixerTest extends AbstractMiddlewareTestCase
{
    private function makeMiddleware(): CacheHashFixer
    {
        return GeneralUtility::makeInstance(CacheHashFixer::class);
    }

    private function makeRequest(string $path, mixed $site = 'default'): ServerRequest
    {
        $request = new ServerRequest(new Uri('https://example.com' . $path), 'GET');

        if ($site === 'default') {
            $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        }

        return $request->withAttribute('site', $site);
    }

    #[Test]
    public function passes_request_to_handler_for_api_path(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = false;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = false;

        $request = $this->makeRequest('/api/v1/my-endpoint');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);
    }

    #[Test]
    public function temporarily_disables_cache_hash_enforcement_for_api_path(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $request = $this->makeRequest('/api/v1/my-endpoint');

        $capturedPageNotFound = null;
        $capturedEnforceValidation = null;

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function () use (&$capturedPageNotFound, &$capturedEnforceValidation): ResponseInterface {
            $capturedPageNotFound = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'];
            $capturedEnforceValidation = $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'];
            return new JsonResponse([]);
        });

        // Act
        $middleware->process($request, $handler);

        // Assert — values were disabled during handler execution
        self::assertFalse($capturedPageNotFound);
        self::assertFalse($capturedEnforceValidation);

        // Assert — original values are restored after execution
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError']);
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation']);
    }

    #[Test]
    public function restores_globals_even_when_handler_throws(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $request = $this->makeRequest('/api/v1/my-endpoint');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willThrowException(new RuntimeException('Handler failed'));

        // Act + Assert exception propagates
        try {
            $middleware->process($request, $handler);
            self::fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $runtimeException) {
            self::assertSame('Handler failed', $runtimeException->getMessage());
        }

        // Assert — globals are restored even after exception
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError']);
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation']);
    }

    #[Test]
    public function passes_through_for_non_api_path(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $request = $this->makeRequest('/some-page');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);

        // No assertions on globals needed — handler was called without modifying cache hash settings
    }

    #[Test]
    public function passes_through_when_site_attribute_is_null(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $request = $this->makeRequest('/api/v1/my-endpoint', null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);
    }

    #[Test]
    public function passes_through_when_site_is_null_site(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $request = $this->makeRequest('/api/v1/my-endpoint', new NullSite());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);
    }
}
