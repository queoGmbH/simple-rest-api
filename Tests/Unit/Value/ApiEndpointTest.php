<?php

declare(strict_types=1);

namespace Unit\Value;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpoint::class)]
final class ApiEndpointTest extends UnitTestCase
{
    #[Test]
    public function provides_endpoint_path_without_parameters(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint('MyApiController', 'myApiMethod', '/v1/my-endpoint/{someArg}/{otherArg}', 'GET', ['someArg', 'otherArg']);

        $this->assertSame('/v1/my-endpoint', $apiEndpoint->getPathWithoutParameters());
    }

    #[Test]
    public function provides_summary_and_description(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            'My Endpoint Summary',
            'This is a detailed description of my endpoint'
        );

        $this->assertSame('My Endpoint Summary', $apiEndpoint->summary);
        $this->assertSame('This is a detailed description of my endpoint', $apiEndpoint->description);
    }

    #[Test]
    public function allows_empty_summary_and_description(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            []
        );

        $this->assertSame('', $apiEndpoint->summary);
        $this->assertSame('', $apiEndpoint->description);
        $this->assertSame([], $apiEndpoint->parameters);
    }

    #[Test]
    public function supports_tags(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            '',
            '',
            [],
            ['authenticated', 'cacheable', 'public']
        );

        $this->assertSame(['authenticated', 'cacheable', 'public'], $apiEndpoint->tags);
    }

    #[Test]
    public function allows_empty_tags(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            []
        );

        $this->assertSame([], $apiEndpoint->tags);
    }

    #[Test]
    public function checks_if_has_specific_tag(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            '',
            '',
            [],
            ['authenticated', 'cacheable']
        );

        $this->assertTrue($apiEndpoint->hasTag('authenticated'));
        $this->assertTrue($apiEndpoint->hasTag('cacheable'));
        $this->assertFalse($apiEndpoint->hasTag('admin-only'));
    }

    #[Test]
    public function checks_if_has_any_tag(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            '',
            '',
            [],
            ['authenticated', 'cacheable']
        );

        $this->assertTrue($apiEndpoint->hasAnyTag(['authenticated', 'admin-only']));
        $this->assertTrue($apiEndpoint->hasAnyTag(['cacheable']));
        $this->assertFalse($apiEndpoint->hasAnyTag(['admin-only', 'public']));
    }

    #[Test]
    public function checks_if_has_all_tags(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            [],
            '',
            '',
            [],
            ['authenticated', 'cacheable', 'public']
        );

        $this->assertTrue($apiEndpoint->hasAllTags(['authenticated', 'cacheable']));
        $this->assertTrue($apiEndpoint->hasAllTags(['public']));
        $this->assertFalse($apiEndpoint->hasAllTags(['authenticated', 'admin-only']));
    }

    #[Test]
    public function checks_if_is_specific_endpoint(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            []
        );

        $this->assertTrue($apiEndpoint->isEndpoint('MyApiController', 'myApiMethod'));
        $this->assertFalse($apiEndpoint->isEndpoint('OtherController', 'myApiMethod'));
        $this->assertFalse($apiEndpoint->isEndpoint('MyApiController', 'otherMethod'));
    }

    #[Test]
    public function matches_path_pattern(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $this->assertTrue($apiEndpoint->matchesPath('/v1/users/{userId}'));
        $this->assertFalse($apiEndpoint->matchesPath('/v1/products/{productId}'));
    }

    #[Test]
    public function returns_class_name(): void // phpcs:ignore
    {
        $apiEndpoint = new ApiEndpoint(
            'MyApiController',
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            []
        );

        $this->assertSame('MyApiController', $apiEndpoint->getClass());
    }
}
