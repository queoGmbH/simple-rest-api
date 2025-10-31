<?php

declare(strict_types=1);

use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\DependencyInjection\ApiEndpointProviderPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
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
