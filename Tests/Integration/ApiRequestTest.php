<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ApiResolverMiddleware::class)]
final class ApiRequestTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/simple_rest_api',
    ];

    #[Test]
    public function dummy_test(): void
    {
        $middleware = GeneralUtility::makeInstance(ApiResolverMiddleware::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/lang/api/v1/my/api-endpoint';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $site = $this->createMock(SiteInterface::class);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('site', $site);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
