<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Value;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpoint::class)]
final class ApiEndpointTest extends UnitTestCase
{
    #[Test]
    public function provides_endpoint_path_without_parameters(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint($className, 'myApiMethod', '/v1/my-endpoint/{someArg}/{otherArg}', 'GET', new ApiEndpointParameterCollection());

        $this->assertSame('/v1/my-endpoint', $apiEndpoint->getPathWithoutParameters());
    }

    #[Test]
    public function provides_summary_and_description(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection(),
            'My Endpoint Summary',
            'This is a detailed description of my endpoint'
        );

        $this->assertSame('My Endpoint Summary', $apiEndpoint->summary);
        $this->assertSame('This is a detailed description of my endpoint', $apiEndpoint->description);
    }

    #[Test]
    public function allows_empty_summary_and_description(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        $this->assertSame('', $apiEndpoint->summary);
        $this->assertSame('', $apiEndpoint->description);
        $this->assertTrue($apiEndpoint->parameters->isEmpty());
    }

    #[Test]
    public function supports_tags(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection(),
            '',
            '',
            ['authenticated', 'cacheable', 'public']
        );

        $this->assertSame(['authenticated', 'cacheable', 'public'], $apiEndpoint->tags);
    }

    #[Test]
    public function allows_empty_tags(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        $this->assertSame([], $apiEndpoint->tags);
    }

    #[Test]
    public function checks_if_has_specific_tag(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection(),
            '',
            '',
            ['authenticated', 'cacheable']
        );

        $this->assertTrue($apiEndpoint->hasTag('authenticated'));
        $this->assertTrue($apiEndpoint->hasTag('cacheable'));
        $this->assertFalse($apiEndpoint->hasTag('admin-only'));
    }

    #[Test]
    public function checks_if_has_any_tag(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection(),
            '',
            '',
            ['authenticated', 'cacheable']
        );

        $this->assertTrue($apiEndpoint->hasAnyTag(['authenticated', 'admin-only']));
        $this->assertTrue($apiEndpoint->hasAnyTag(['cacheable']));
        $this->assertFalse($apiEndpoint->hasAnyTag(['admin-only', 'public']));
    }

    #[Test]
    public function checks_if_has_all_tags(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection(),
            '',
            '',
            ['authenticated', 'cacheable', 'public']
        );

        $this->assertTrue($apiEndpoint->hasAllTags(['authenticated', 'cacheable']));
        $this->assertTrue($apiEndpoint->hasAllTags(['public']));
        $this->assertFalse($apiEndpoint->hasAllTags(['authenticated', 'admin-only']));
    }

    #[Test]
    public function checks_if_is_specific_endpoint(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        $this->assertTrue($apiEndpoint->isEndpoint($className, 'myApiMethod'));
        /** @phpstan-var class-string $otherClassName */
        $otherClassName = 'OtherController';
        $this->assertFalse($apiEndpoint->isEndpoint($otherClassName, 'myApiMethod'));
        $this->assertFalse($apiEndpoint->isEndpoint($className, 'otherMethod'));
    }

    #[Test]
    public function checks_if_is_any_endpoint_with_single_method(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'MyApiController' => 'myApiMethod',
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'MyApiController' => 'myApiMethod',
            'OtherController' => 'otherMethod',
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'OtherController' => 'myApiMethod',
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'MyApiController' => 'otherMethod',
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));
    }

    #[Test]
    public function checks_if_is_any_endpoint_with_multiple_methods(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        /** @phpstan-var array<class-string, array<string>> $endpoints */
        $endpoints = [
            'MyApiController' => ['myApiMethod', 'otherMethod'],
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, array<string>> $endpoints */
        $endpoints = [
            'MyApiController' => ['firstMethod', 'myApiMethod', 'thirdMethod'],
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, array<string>> $endpoints */
        $endpoints = [
            'MyApiController' => ['firstMethod', 'secondMethod'],
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, array<string>> $endpoints */
        $endpoints = [
            'OtherController' => ['myApiMethod'],
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));
    }

    #[Test]
    public function checks_if_is_any_endpoint_with_wildcard(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'MyApiController' => '*',
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'OtherController' => 'someMethod',
            'MyApiController' => '*',
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, string> $endpoints */
        $endpoints = [
            'OtherController' => '*',
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));
    }

    #[Test]
    public function checks_if_is_any_endpoint_with_mixed_criteria(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'UserController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'getUser',
            '/v1/users/{userId}',
            'GET',
            new ApiEndpointParameterCollection()
        );

        /** @phpstan-var array<class-string, array<string>|string> $endpoints */
        $endpoints = [
            'UserController' => ['getUser', 'updateUser'],
            'ProductController' => '*',
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, array<string>|string> $endpoints */
        $endpoints = [
            'ProductController' => 'getProduct',
            'UserController' => 'getUser',
            'OrderController' => ['listOrders', 'createOrder'],
        ];
        $this->assertTrue($apiEndpoint->isAnyEndpoint($endpoints));

        /** @phpstan-var array<class-string, array<string>|string> $endpoints */
        $endpoints = [
            'UserController' => ['updateUser', 'deleteUser'],
            'ProductController' => '*',
        ];
        $this->assertFalse($apiEndpoint->isAnyEndpoint($endpoints));
    }

    #[Test]
    public function matches_path_pattern(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/users/{userId}',
            'GET',
            new ApiEndpointParameterCollection()
        );

        $this->assertTrue($apiEndpoint->matchesPath('/v1/users/{userId}'));
        $this->assertFalse($apiEndpoint->matchesPath('/v1/products/{productId}'));
    }

    #[Test]
    public function returns_class_name(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/my-endpoint',
            'GET',
            new ApiEndpointParameterCollection()
        );

        $this->assertSame($className, $apiEndpoint->getClass());
    }

    #[Test]
    public function counts_total_parameters_including_request(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/endpoint/{id}',
            'GET',
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('id', 'int'),
                new ApiEndpointParameter('request', ServerRequestInterface::class)
            )
        );

        $this->assertSame(2, $apiEndpoint->parameterCount());
    }

    #[Test]
    public function counts_path_parameters_excluding_server_request(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/endpoint/{id}',
            'GET',
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('id', 'int'),
                new ApiEndpointParameter('request', ServerRequestInterface::class)
            )
        );

        $this->assertSame(1, $apiEndpoint->pathParameterCount());
    }

    #[Test]
    public function counts_path_parameters_as_zero_when_only_request_parameter_exists(): void // phpcs:ignore
    {
        /** @phpstan-var class-string $className */
        $className = 'MyApiController';
        $apiEndpoint = new ApiEndpoint(
            $className,
            'myApiMethod',
            '/v1/endpoint',
            'GET',
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('request', ServerRequestInterface::class)
            )
        );

        $this->assertSame(0, $apiEndpoint->pathParameterCount());
    }
}
