<?php

declare(strict_types=1);

use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\DependencyInjection\ApiEndpointProviderPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Register main extension controllers
    $services->load('Queo\\SimpleRestApi\\Controller\\', __DIR__ . '/../Classes/Controller/');

    // Register test fixtures for E2E testing (only in dev/test environments)
    if (class_exists('Queo\\SimpleRestApi\\Tests\\Fixtures\\TestController')) {
        $services->load('Queo\\SimpleRestApi\\Tests\\Fixtures\\', __DIR__ . '/../Tests/Fixtures/')
            ->exclude(__DIR__ . '/../Tests/Fixtures/{DependencyInjection}');
    }

    $containerBuilder->registerAttributeForAutoconfiguration(
        AsApiEndpoint::class,
        static function (ChildDefinition $definition, AsApiEndpoint $attribute, Reflector $reflector): void {
            $definition->addTag(
                AsApiEndpoint::TAG_NAME,
                [
                    'method' => $reflector instanceof ReflectionMethod ? $reflector->getName() : null,
                    'http_method' => $attribute->method,
                    'path' => $attribute->path,
                    'summary' => $attribute->summary,
                    'description' => $attribute->description,
                ]
            );
        }
    );

    $containerBuilder->addCompilerPass(new ApiEndpointProviderPass('api.endpoint'));
};
