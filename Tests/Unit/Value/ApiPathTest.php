<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Value\ApiPath;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiPath::class)]
final class ApiPathTest extends UnitTestCase
{
    #[Test]
    public function knows_if_request_uri_is_api_uri(): void
    {
        $siteBase = new Uri('/de/');
        $requestUri = new Uri('/de/api/v1/my-endpoint');
        $apiBasePath = '/api/';

        $apiPath = new ApiPath($siteBase, $requestUri, $apiBasePath);

        $this->assertTrue($apiPath->isApiPath());
    }

    #[Test]
    public function holds_endpoint_path(): void
    {
        $siteBase = new Uri('/de/');
        $requestUri = new Uri('/de/api/v1/my-endpoint');
        $apiBasePath = '/api/';

        $apiPath = new ApiPath($siteBase, $requestUri, $apiBasePath);

        $this->assertSame('/api/v1/my-endpoint', $apiPath->getEndpointPath());
    }
}
