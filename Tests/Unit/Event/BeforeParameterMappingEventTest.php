<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Event;

use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Event\BeforeParameterMappingEvent;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(BeforeParameterMappingEvent::class)]
final class BeforeParameterMappingEventTest extends UnitTestCase
{
    private function makeEndpoint(): ApiEndpoint
    {
        /** @phpstan-var class-string $className */
        $className = 'TestController';
        return new ApiEndpoint($className, 'testMethod', '/v1/test', 'GET', new ApiEndpointParameterCollection());
    }

    private function makeResolver(): EndpointParameterResolver
    {
        $request = $this->createMock(ServerRequestInterface::class);
        return new EndpointParameterResolver(new ApiEndpointParameterCollection(), [], $request);
    }

    private function makeApiRequest(): ApiRequest
    {
        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        $serverRequest = (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);
        $config = new ExtensionConfiguration($serverRequest);
        return new ApiRequest($serverRequest, $config);
    }

    #[Test]
    public function constructor_stores_endpoint_correctly(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $resolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();

        // Act
        $event = new BeforeParameterMappingEvent($resolver, $endpoint, $apiRequest);

        // Assert
        self::assertSame($endpoint, $event->getApiEndpoint());
    }

    #[Test]
    public function constructor_stores_request_correctly(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $resolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();

        // Act
        $event = new BeforeParameterMappingEvent($resolver, $endpoint, $apiRequest);

        // Assert
        self::assertSame($apiRequest, $event->getApiRequest());
    }

    #[Test]
    public function constructor_stores_resolver_correctly(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $resolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();

        // Act
        $event = new BeforeParameterMappingEvent($resolver, $endpoint, $apiRequest);

        // Assert
        self::assertSame($resolver, $event->getPathParameters());
    }

    #[Test]
    public function override_parameters_replaces_resolver(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $originalResolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();
        $event = new BeforeParameterMappingEvent($originalResolver, $endpoint, $apiRequest);

        $replacementResolver = $this->makeResolver();

        // Act
        $event->overrideParameters($replacementResolver);

        // Assert
        self::assertSame($replacementResolver, $event->getPathParameters());
    }

    #[Test]
    public function override_parameters_does_not_affect_endpoint(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $originalResolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();
        $event = new BeforeParameterMappingEvent($originalResolver, $endpoint, $apiRequest);

        // Act
        $event->overrideParameters($this->makeResolver());

        // Assert
        self::assertSame($endpoint, $event->getApiEndpoint());
    }

    #[Test]
    public function override_parameters_does_not_affect_request(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $originalResolver = $this->makeResolver();
        $apiRequest = $this->makeApiRequest();
        $event = new BeforeParameterMappingEvent($originalResolver, $endpoint, $apiRequest);

        // Act
        $event->overrideParameters($this->makeResolver());

        // Assert
        self::assertSame($apiRequest, $event->getApiRequest());
    }
}
