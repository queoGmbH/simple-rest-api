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
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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

    #[Test]
    public function uses_language_base_path_when_site_language_is_present(): void // phpcs:ignore
    {
        // Arrange
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/de/api/v1/my-endpoint');
        $request = new ServerRequest($uri, 'GET');
        $request = $request->withAttribute('site', $site);

        $siteLanguage = $this->createStub(SiteLanguage::class);
        $siteLanguage->method('getBase')->willReturn(new Uri('https://example.com/de/'));
        $request = $request->withAttribute('language', $siteLanguage);

        $capturedPageNotFound = null;
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function () use (&$capturedPageNotFound): ResponseInterface {
            $capturedPageNotFound = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'];
            return new JsonResponse([]);
        });

        // Act
        $middleware->process($request, $handler);

        // Assert — cache hash was disabled during handler execution (language-based path matched)
        self::assertFalse($capturedPageNotFound);
    }

    /**
     * @param array<int, array<string, mixed>> $extraLanguages
     */
    private function makeSubdirSite(array $extraLanguages = []): Site
    {
        $languages = array_merge(
            [['languageId' => 0, 'title' => 'English', 'enabled' => true,
              'base' => '/', 'locale' => 'en_US.UTF-8', 'navigationTitle' => 'English', 'flag' => 'us']],
            $extraLanguages
        );

        return new Site('subdir-site', 1, [
            'base' => 'http://localhost:8080/subdir/',
            'settings' => [],
            'languages' => $languages,
        ]);
    }

    #[Test]
    public function disables_cache_hash_for_subdirectory_site_default_language(): void // phpcs:ignore
    {
        // Arrange — site lives under /subdir/ on the same host; TYPO3's Site constructor
        // resolves language base '/' to the full URL 'http://localhost:8080/subdir/', so
        // getBase()->getPath() returns '/subdir/' — the prefix is included automatically.
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $site = $this->makeSubdirSite();
        $uri = new Uri('http://localhost:8080/subdir/api/v1/my-endpoint');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getLanguageById(0));

        $capturedPageNotFound = null;
        $capturedEnforceValidation = null;
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(
            function () use (&$capturedPageNotFound, &$capturedEnforceValidation): ResponseInterface {
                $capturedPageNotFound = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'];
                $capturedEnforceValidation = $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'];
                return new JsonResponse([]);
            }
        );

        // Act
        $middleware->process($request, $handler);

        // Assert — both globals were disabled, confirming '/subdir/api/' matched
        self::assertFalse($capturedPageNotFound);
        self::assertFalse($capturedEnforceValidation);
    }

    #[Test]
    public function disables_cache_hash_for_subdirectory_site_with_language_prefix(): void // phpcs:ignore
    {
        // Arrange — language with base '/de/' resolves to 'http://localhost:8080/subdir/de/',
        // so the compare path becomes '/subdir/de/api/' which must match the request.
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $site = $this->makeSubdirSite([
            ['languageId' => 1, 'title' => 'German', 'enabled' => true,
             'base' => '/de/', 'locale' => 'de_DE.UTF-8', 'navigationTitle' => 'German', 'flag' => 'de'],
        ]);
        $uri = new Uri('http://localhost:8080/subdir/de/api/v1/my-endpoint');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getLanguageById(1));

        $capturedPageNotFound = null;
        $capturedEnforceValidation = null;
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(
            function () use (&$capturedPageNotFound, &$capturedEnforceValidation): ResponseInterface {
                $capturedPageNotFound = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'];
                $capturedEnforceValidation = $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'];
                return new JsonResponse([]);
            }
        );

        // Act
        $middleware->process($request, $handler);

        // Assert
        self::assertFalse($capturedPageNotFound);
        self::assertFalse($capturedEnforceValidation);
    }

    #[Test]
    public function passes_through_for_non_api_path_on_subdirectory_site(): void // phpcs:ignore
    {
        // Arrange — request targets a regular page, not the API — must not touch globals
        $middleware = $this->makeMiddleware();

        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = true;

        $site = $this->makeSubdirSite();
        $uri = new Uri('http://localhost:8080/subdir/some-page');
        $request = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getLanguageById(0));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new JsonResponse([]));

        // Act
        $middleware->process($request, $handler);

        // Assert — globals must be untouched (pass-through, not an API path)
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError']);
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation']);
    }
}
