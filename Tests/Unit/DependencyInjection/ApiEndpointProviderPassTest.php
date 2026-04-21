<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\DependencyInjection\ApiEndpointProviderPass;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointProviderPass::class)]
final class ApiEndpointProviderPassTest extends UnitTestCase
{
    #[Test]
    public function returns_early_when_no_api_endpoint_provider_definition_exists(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();
        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act — must not throw
        $pass->process($container);

        // Assert — no exception thrown, no definition registered
        self::assertFalse($container->hasDefinition(ApiEndpointProvider::class));
    }

    #[Test]
    public function adds_method_call_for_each_tagged_service(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'myMethod',
            'http_method' => 'GET',
            'path' => '/v1/my-endpoint',
            'summary' => 'My endpoint summary',
            'description' => 'My endpoint description',
            'tags' => ['public'],
        ]);
        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        $methodCalls = $providerDefinition->getMethodCalls();
        self::assertCount(1, $methodCalls);
        self::assertSame('addEndpoint', $methodCalls[0][0]);
    }

    #[Test]
    public function adds_correct_arguments_to_method_call(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'myMethod',
            'http_method' => 'POST',
            'path' => '/v1/resource',
            'summary' => 'Create resource',
            'description' => 'Creates a new resource',
            'tags' => ['authenticated'],
        ]);
        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        $methodCalls = $providerDefinition->getMethodCalls();
        $args = $methodCalls[0][1];
        self::assertSame('MyController', $args[0]);
        self::assertSame('myMethod', $args[1]);
        self::assertSame('POST', $args[2]);
        self::assertSame('/v1/resource', $args[3]);
        self::assertSame('Create resource', $args[4]);
        self::assertSame('Creates a new resource', $args[5]);
        self::assertSame(['authenticated'], $args[6]);
    }

    #[Test]
    public function uses_get_as_default_http_method_when_not_specified(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'myMethod',
            'path' => '/v1/resource',
        ]);
        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        $methodCalls = $providerDefinition->getMethodCalls();
        $args = $methodCalls[0][1];
        self::assertSame('GET', $args[2]);
    }

    #[Test]
    public function sets_tagged_service_as_public(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        $controllerDefinition->setPublic(false);
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'myMethod',
            'path' => '/v1/resource',
        ]);
        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        self::assertTrue($container->getDefinition('MyController')->isPublic());
    }

    #[Test]
    public function adds_method_call_for_each_tag_on_a_service(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'getResource',
            'http_method' => 'GET',
            'path' => '/v1/resource',
        ]);
        $controllerDefinition->addTag('simple_rest_api.endpoint', [
            'method' => 'createResource',
            'http_method' => 'POST',
            'path' => '/v1/resource',
        ]);
        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        $methodCalls = $providerDefinition->getMethodCalls();
        self::assertCount(2, $methodCalls);
    }

    #[Test]
    public function uses_empty_string_defaults_for_optional_tag_attributes(): void // phpcs:ignore
    {
        // Arrange
        $container = new ContainerBuilder();

        $providerDefinition = new Definition(ApiEndpointProvider::class);
        $container->setDefinition(ApiEndpointProvider::class, $providerDefinition);

        $controllerDefinition = new Definition('MyController');
        // Only provide required attributes, omit optional ones
        $controllerDefinition->addTag('simple_rest_api.endpoint', []);

        $container->setDefinition('MyController', $controllerDefinition);

        $pass = new ApiEndpointProviderPass('simple_rest_api.endpoint');

        // Act
        $pass->process($container);

        // Assert
        $methodCalls = $providerDefinition->getMethodCalls();
        $args = $methodCalls[0][1];
        self::assertSame('', $args[1]); // method defaults to ''
        self::assertSame('GET', $args[2]); // http_method defaults to 'GET'
        self::assertSame('', $args[3]); // path defaults to ''
        self::assertSame('', $args[4]); // summary defaults to ''
        self::assertSame('', $args[5]); // description defaults to ''
        self::assertSame([], $args[6]); // tags defaults to []
    }
}
