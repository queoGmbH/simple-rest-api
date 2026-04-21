<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Event\AfterParameterMappingEvent;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(AfterParameterMappingEvent::class)]
final class AfterParameterMappingEventTest extends UnitTestCase
{
    private function makeEndpoint(): ApiEndpoint
    {
        /** @phpstan-var class-string $className */
        $className = 'TestController';
        return new ApiEndpoint($className, 'testMethod', '/v1/test', 'GET', new ApiEndpointParameterCollection());
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
        $apiRequest = $this->makeApiRequest();
        $params = ['foo', 42];

        // Act
        $event = new AfterParameterMappingEvent($params, $endpoint, $apiRequest);

        // Assert
        self::assertSame($endpoint, $event->getEndpoint());
    }

    #[Test]
    public function constructor_stores_request_correctly(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();
        $params = ['foo', 42];

        // Act
        $event = new AfterParameterMappingEvent($params, $endpoint, $apiRequest);

        // Assert
        self::assertSame($apiRequest, $event->getApiRequest());
    }

    #[Test]
    public function constructor_stores_method_parameters_correctly(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();
        $params = ['foo', 42, true];

        // Act
        $event = new AfterParameterMappingEvent($params, $endpoint, $apiRequest);

        // Assert
        self::assertSame($params, $event->getMethodParameters());
    }

    #[Test]
    public function override_method_parameters_replaces_params_array(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();
        $originalParams = ['original', 1];
        $event = new AfterParameterMappingEvent($originalParams, $endpoint, $apiRequest);

        $newParams = ['replaced', 99, false];

        // Act
        $event->overrideMethodParameters($newParams);

        // Assert
        self::assertSame($newParams, $event->getMethodParameters());
    }

    #[Test]
    public function override_method_parameters_does_not_affect_endpoint(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();
        $event = new AfterParameterMappingEvent([], $endpoint, $apiRequest);

        // Act
        $event->overrideMethodParameters(['something']);

        // Assert
        self::assertSame($endpoint, $event->getEndpoint());
    }

    #[Test]
    public function override_method_parameters_does_not_affect_request(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();
        $event = new AfterParameterMappingEvent([], $endpoint, $apiRequest);

        // Act
        $event->overrideMethodParameters(['something']);

        // Assert
        self::assertSame($apiRequest, $event->getApiRequest());
    }

    #[Test]
    public function allows_empty_params_array(): void // phpcs:ignore
    {
        // Arrange
        $endpoint = $this->makeEndpoint();
        $apiRequest = $this->makeApiRequest();

        // Act
        $event = new AfterParameterMappingEvent([], $endpoint, $apiRequest);

        // Assert
        self::assertSame([], $event->getMethodParameters());
    }
}
