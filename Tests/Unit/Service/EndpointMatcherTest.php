<?php

declare(strict_types=1);

namespace Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Service\EndpointMatcher;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EndpointMatcher::class)]
final class EndpointMatcherTest extends UnitTestCase
{
    private EndpointMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new EndpointMatcher();
    }

    #[Test]
    public function matches_single_endpoint_with_before_parameter_mapping_event(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => 'getUser',
        ]);

        $this->assertTrue($matches);
    }

    #[Test]
    public function does_not_match_different_endpoint(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => 'updateUser',
        ]);

        $this->assertFalse($matches);
    }

    #[Test]
    public function matches_multiple_methods_on_same_controller(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'updateUser',
            '/v1/users/{userId}',
            'PUT',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => ['getUser', 'updateUser', 'deleteUser'],
        ]);

        $this->assertTrue($matches);
    }

    #[Test]
    public function matches_wildcard_for_all_methods(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'anyMethod',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => '*',
        ]);

        $this->assertTrue($matches);
    }

    #[Test]
    public function matches_multiple_controllers(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'ProductController',
            'getProduct',
            '/v1/products/{productId}',
            'GET',
            ['productId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => ['getUser'],
            'ProductController' => 'getProduct',
        ]);

        $this->assertTrue($matches);
    }

    #[Test]
    public function checks_single_tag(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId'],
            '',
            '',
            [],
            ['authenticated', 'cacheable']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->hasTag($event, 'authenticated'));
        $this->assertTrue($this->matcher->hasTag($event, 'cacheable'));
        $this->assertFalse($this->matcher->hasTag($event, 'admin-only'));
    }

    #[Test]
    public function checks_any_tag(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId'],
            '',
            '',
            [],
            ['authenticated', 'cacheable']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->hasAnyTag($event, ['authenticated', 'admin-only']));
        $this->assertFalse($this->matcher->hasAnyTag($event, ['admin-only', 'public']));
    }

    #[Test]
    public function checks_all_tags(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId'],
            '',
            '',
            [],
            ['authenticated', 'cacheable', 'public']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->hasAllTags($event, ['authenticated', 'cacheable']));
        $this->assertFalse($this->matcher->hasAllTags($event, ['authenticated', 'admin-only']));
    }

    #[Test]
    public function matches_exact_path(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->matchesPath($event, '/v1/users/{userId}'));
        $this->assertFalse($this->matcher->matchesPath($event, '/v1/products/{productId}'));
    }

    #[Test]
    public function matches_path_with_wildcards(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}/posts',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->matchesPath($event, '/v1/users/*'));
        $this->assertTrue($this->matcher->matchesPath($event, '/v1/*/posts'));
        $this->assertFalse($this->matcher->matchesPath($event, '/v2/*'));
    }

    #[Test]
    public function checks_http_method(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new BeforeParameterMappingEvent(
            new Parameters([]),
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $this->assertTrue($this->matcher->usesHttpMethod($event, 'GET'));
        $this->assertTrue($this->matcher->usesHttpMethod($event, 'get')); // Case insensitive
        $this->assertFalse($this->matcher->usesHttpMethod($event, 'POST'));
    }

    #[Test]
    public function works_with_after_parameter_mapping_event(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new AfterParameterMappingEvent(
            [],
            $endpoint,
            $this->createMock(ApiRequest::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => 'getUser',
        ]);

        $this->assertTrue($matches);
    }

    #[Test]
    public function works_with_modify_api_response_event(): void // phpcs:ignore
    {
        $endpoint = new ApiEndpoint(
            'UserController',
            'getUser',
            '/v1/users/{userId}',
            'GET',
            ['userId']
        );

        $event = new ModifyApiResponseEvent(
            $this->createMock(ResponseInterface::class),
            $endpoint,
            $this->createMock(\Queo\SimpleRestApi\Http\ApiRequestInterface::class)
        );

        $matches = $this->matcher->matches($event, [
            'UserController' => 'getUser',
        ]);

        $this->assertTrue($matches);
    }
}
