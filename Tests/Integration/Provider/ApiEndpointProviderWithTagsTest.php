<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointProvider::class)]
final class ApiEndpointProviderWithTagsTest extends UnitTestCase
{
    #[Test]
    public function endpoint_provider_stores_tags(): void // phpcs:ignore
    {
        $provider = new ApiEndpointProvider();
        $provider->addEndpoint(
            'MyController',
            'myMethod',
            'GET',
            '/v1/my-endpoint',
            'Summary',
            'Description',
            ['authenticated', 'cacheable', 'public']
        );

        $endpoints = $provider->getAllEndpoints();

        $this->assertCount(1, $endpoints);
        $this->assertSame(['authenticated', 'cacheable', 'public'], $endpoints[0]->tags);
    }

    #[Test]
    public function endpoint_provider_allows_empty_tags(): void // phpcs:ignore
    {
        $provider = new ApiEndpointProvider();
        $provider->addEndpoint(
            'MyController',
            'myMethod',
            'GET',
            '/v1/my-endpoint'
        );

        $endpoints = $provider->getAllEndpoints();

        $this->assertCount(1, $endpoints);
        $this->assertSame([], $endpoints[0]->tags);
    }

    #[Test]
    public function endpoint_can_check_tags(): void // phpcs:ignore
    {
        $provider = new ApiEndpointProvider();
        $provider->addEndpoint(
            'MyController',
            'myMethod',
            'GET',
            '/v1/my-endpoint',
            '',
            '',
            ['authenticated', 'cacheable']
        );

        $endpoints = $provider->getAllEndpoints();
        $endpoint = $endpoints[0];

        $this->assertTrue($endpoint->hasTag('authenticated'));
        $this->assertTrue($endpoint->hasTag('cacheable'));
        $this->assertFalse($endpoint->hasTag('admin-only'));

        $this->assertTrue($endpoint->hasAnyTag(['authenticated', 'admin-only']));
        $this->assertFalse($endpoint->hasAnyTag(['admin-only', 'public']));

        $this->assertTrue($endpoint->hasAllTags(['authenticated', 'cacheable']));
        $this->assertFalse($endpoint->hasAllTags(['authenticated', 'admin-only']));
    }
}
